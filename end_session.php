<?php
session_start();
include "config/db.php";
date_default_timezone_set('Asia/Manila');

$isAjax = isset($_GET['ajax']) && $_GET['ajax'] == '1';

function jsonOut($ok, $msg, $extra = []) {
    header('Content-Type: application/json');
    echo json_encode(array_merge(['ok' => $ok, 'msg' => $msg], $extra));
    exit();
}

if (!isset($_GET['id'])) {
    if ($isAjax) jsonOut(false, 'no_id');
    header("Location: counter.php"); exit();
}

$pc_id    = intval($_GET['id']);
$redirect = $_GET['redirect'] ?? null;
$end_time = date("Y-m-d H:i:s");

try {
    // 1. Check PC exists and is active
    $check = $pdo->prepare("SELECT status FROM pcs WHERE id = :id");
    $check->execute([':id' => $pc_id]);
    $pc = $check->fetch();

    if (!$pc) {
        if ($isAjax) jsonOut(false, 'pc_not_found');
        header("Location: counter.php"); exit();
    }

    if ($pc['status'] === 'available') {
        // Already ended — treat as success
        if ($isAjax) jsonOut(true, 'already_ended');
        header("Location: counter.php"); exit();
    }

    // 2. Find the active session
    $stmt = $pdo->prepare("SELECT id, start_time, time_limit FROM sessions WHERE pc_id = :pc AND end_time IS NULL ORDER BY id DESC LIMIT 1");
    $stmt->execute([':pc' => $pc_id]);
    $row = $stmt->fetch();

    if (!$row) {
        // No session row but PC marked active — fix the orphan
        $pdo->prepare("UPDATE pcs SET status = 'available' WHERE id = :id")
            ->execute([':id' => $pc_id]);
        if ($isAjax) jsonOut(true, 'orphan_fixed');
        header("Location: counter.php"); exit();
    }

    $session_id = $row['id'];
    $start_time = $row['start_time'];
    $time_limit = $row['time_limit'];

    // 3. Calculate cost
    $cost = 0;
    try {
        $rates = $pdo->query("SELECT * FROM settings WHERE id = 1")->fetch();
        $start_dt      = new DateTime($start_time);
        $end_dt        = new DateTime($end_time);
        $diff          = $start_dt->diff($end_dt);
        $total_minutes = ($diff->h * 60) + $diff->i + ($diff->days * 24 * 60);

        if ($time_limit) {
            $pkgQuery = $pdo->prepare("SELECT price FROM packages WHERE minutes = :m LIMIT 1");
            $pkgQuery->execute([':m' => $time_limit]);
            $pkgRow = $pkgQuery->fetch();
            $cost = $pkgRow ? $pkgRow['price'] : max($rates['minimum_charge'] ?? 0, ($total_minutes / 60) * ($rates['hourly_rate'] ?? 0));
        } else {
            $cost = max($rates['minimum_charge'] ?? 0, ($total_minutes / 60) * ($rates['hourly_rate'] ?? 0));
        }
    } catch (Exception $e) {
        $cost = 0; // Cost calc failed — still end the session
    }

    // 4. Get PC name
    $pc_name = 'PC-' . $pc_id;
    try {
        $pc_row = $pdo->prepare("SELECT name FROM pcs WHERE id = :id");
        $pc_row->execute([':id' => $pc_id]);
        $fetched = $pc_row->fetch();
        if ($fetched) $pc_name = $fetched['name'];
    } catch (Exception $e) {}

    // 5. Update session end_time and cost — CRITICAL
    $pdo->prepare("UPDATE sessions SET end_time = :et, cost = :cost WHERE id = :id")
        ->execute([':et' => $end_time, ':cost' => $cost, ':id' => $session_id]);

    // 6. Mark PC available — CRITICAL
    $pdo->prepare("UPDATE pcs SET status = 'available' WHERE id = :id")
        ->execute([':id' => $pc_id]);

    // 7. Log transaction — non-critical, wrapped separately
    try {
        $pdo->prepare("INSERT INTO transactions (type, description, amount, time) VALUES ('Session', :desc, :amt, :t)")
            ->execute([':desc' => $pc_name, ':amt' => $cost, ':t' => $end_time]);
    } catch (Exception $e) {
        // Transaction log failed — session still ended, ignore
    }

    if ($isAjax) jsonOut(true, 'ended', ['pc_name' => $pc_name, 'cost' => $cost]);
    if ($redirect) { header("Location: " . $redirect); exit(); }
    header("Location: counter.php?status=ended&paid=$cost&pc=$pc_name"); exit();

} catch (Exception $e) {
    if ($isAjax) jsonOut(false, 'exception: ' . $e->getMessage());
    header("Location: counter.php"); exit();
}
?>

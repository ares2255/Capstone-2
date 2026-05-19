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
    // 1. Check PC exists
    $check = $pdo->prepare("SELECT status, name FROM pcs WHERE id = :id");
    $check->execute([':id' => $pc_id]);
    $pc = $check->fetch();

    if (!$pc) {
        if ($isAjax) jsonOut(false, 'pc_not_found');
        header("Location: counter.php"); exit();
    }

    $pc_name = $pc['name'];

    // 2. Close ALL open sessions for this PC (fixes duplicate orphan buildup)
    $openSessions = $pdo->prepare("SELECT id, start_time, time_limit FROM sessions WHERE pc_id = :pc AND end_time IS NULL ORDER BY id DESC");
    $openSessions->execute([':pc' => $pc_id]);
    $rows = $openSessions->fetchAll();

    if (empty($rows) && $pc['status'] === 'available') {
        // Nothing to do
        if ($isAjax) jsonOut(true, 'already_ended');
        header("Location: counter.php"); exit();
    }

    // 3. Calculate cost from the most recent session only
    $cost = 0;
    if (!empty($rows)) {
        $row = $rows[0]; // most recent
        try {
            $rates = $pdo->query("SELECT * FROM settings WHERE id = 1")->fetch();
            $start_dt      = new DateTime($row['start_time']);
            $end_dt        = new DateTime($end_time);
            $diff          = $start_dt->diff($end_dt);
            $total_minutes = ($diff->days * 24 * 60) + ($diff->h * 60) + $diff->i;

            if ($row['time_limit']) {
                // Try package_id first (most accurate), fall back to minutes match
                $pkgRow = null;
                if (!empty($row['package_id'])) {
                    $pkgQuery = $pdo->prepare("SELECT price FROM packages WHERE id = :id LIMIT 1");
                    $pkgQuery->execute([':id' => $row['package_id']]);
                    $pkgRow = $pkgQuery->fetch();
                }
                if (!$pkgRow) {
                    $pkgQuery = $pdo->prepare("SELECT price FROM packages WHERE minutes = :m ORDER BY id DESC LIMIT 1");
                    $pkgQuery->execute([':m' => $row['time_limit']]);
                    $pkgRow = $pkgQuery->fetch();
                }
                $cost = $pkgRow ? $pkgRow['price'] : max($rates['minimum_charge'] ?? 0, ($total_minutes / 60) * ($rates['hourly_rate'] ?? 0));
            } else {
                $cost = max($rates['minimum_charge'] ?? 0, ($total_minutes / 60) * ($rates['hourly_rate'] ?? 0));
            }
        } catch (Exception $e) {
            $cost = 0;
        }

        // 4. Close ALL open sessions — most recent gets the cost, rest get 0
        foreach ($rows as $i => $r) {
            $sessionCost = ($i === 0) ? $cost : 0;
            $pdo->prepare("UPDATE sessions SET end_time = :et, cost = :cost WHERE id = :id AND end_time IS NULL")
                ->execute([':et' => $end_time, ':cost' => $sessionCost, ':id' => $r['id']]);
        }
    }

    // 5. Mark PC available — always runs
    $pdo->prepare("UPDATE pcs SET status = 'available' WHERE id = :id")
        ->execute([':id' => $pc_id]);

    // 6. Log transaction — non-critical
    try {
        if ($cost > 0) {
            $pdo->prepare("INSERT INTO transactions (type, description, amount, time) VALUES ('Session', :desc, :amt, :t)")
                ->execute([':desc' => $pc_name, ':amt' => $cost, ':t' => $end_time]);
        }
    } catch (Exception $e) { /* ignore */ }

    if ($isAjax) jsonOut(true, 'ended', ['pc_name' => $pc_name, 'cost' => $cost]);
    if ($redirect) { header("Location: " . $redirect); exit(); }
    header("Location: counter.php?status=ended&paid=$cost&pc=$pc_name"); exit();

} catch (Exception $e) {
    if ($isAjax) jsonOut(false, 'exception: ' . $e->getMessage());
    header("Location: counter.php"); exit();
}
?>

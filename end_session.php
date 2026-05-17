<?php
session_start();
include "config/db.php";
date_default_timezone_set('Asia/Manila');

// Detect fetch/AJAX call
$isAjax = isset($_GET['ajax']) && $_GET['ajax'] == '1';

function jsonOut($ok, $msg, $extra = []) {
    header('Content-Type: application/json');
    echo json_encode(array_merge(['ok' => $ok, 'msg' => $msg], $extra));
    exit();
}

if (isset($_GET['id'])) {
    $pc_id    = intval($_GET['id']);
    $redirect = $_GET['redirect'] ?? null;
    $end_time = date("Y-m-d H:i:s");

    $check = $pdo->prepare("SELECT status FROM pcs WHERE id = :id");
    $check->execute([':id' => $pc_id]);
    $pc = $check->fetch();

    if (!$pc) {
        if ($isAjax) jsonOut(false, 'PC not found');
        header("Location: counter.php"); exit();
    }

    if ($pc['status'] === 'available') {
        // Already ended
        if ($isAjax) jsonOut(true, 'already_ended');
        header("Location: counter.php"); exit();
    }

    $rates    = $pdo->query("SELECT * FROM settings WHERE id = 1")->fetch();
    $pkgQuery = $pdo->prepare("SELECT price FROM packages WHERE minutes = :m LIMIT 1");

    $stmt = $pdo->prepare("SELECT id, start_time, time_limit FROM sessions WHERE pc_id = :pc AND end_time IS NULL ORDER BY id DESC LIMIT 1");
    $stmt->execute([':pc' => $pc_id]);
    $row = $stmt->fetch();

    if ($row) {
        $session_id = $row['id'];
        $start_time = $row['start_time'];
        $time_limit = $row['time_limit'];

        $start_dt      = new DateTime($start_time);
        $end_dt        = new DateTime($end_time);
        $total_minutes = ($start_dt->diff($end_dt)->h * 60) + $start_dt->diff($end_dt)->i;

        $cost = 0;
        if ($time_limit) {
            $pkgQuery->execute([':m' => $time_limit]);
            $pkgRow = $pkgQuery->fetch();
            $cost = $pkgRow ? $pkgRow['price'] : max($rates['minimum_charge'] ?? 0, ($total_minutes / 60) * ($rates['hourly_rate'] ?? 0));
        } else {
            $cost = max($rates['minimum_charge'] ?? 0, ($total_minutes / 60) * ($rates['hourly_rate'] ?? 0));
        }

        $pc_row = $pdo->prepare("SELECT name FROM pcs WHERE id = :id");
        $pc_row->execute([':id' => $pc_id]);
        $pc_name = $pc_row->fetch()['name'];

        $pdo->prepare("INSERT INTO transactions (type, description, amount, time) VALUES ('Session', :desc, :amt, :t)")
            ->execute([':desc' => $pc_name, ':amt' => $cost, ':t' => $end_time]);

        $pdo->prepare("UPDATE sessions SET end_time = :et, cost = :cost WHERE id = :id")
            ->execute([':et' => $end_time, ':cost' => $cost, ':id' => $session_id]);

        $pdo->prepare("UPDATE pcs SET status = 'available' WHERE id = :id")
            ->execute([':id' => $pc_id]);

        if ($isAjax) jsonOut(true, 'ended', ['pc_name' => $pc_name, 'cost' => $cost]);

        if ($redirect) { header("Location: " . $redirect); exit(); }
        header("Location: counter.php?status=ended&paid=$cost&pc=$pc_name"); exit();
    }

    if ($isAjax) jsonOut(false, 'no_active_session');
}

if ($isAjax) jsonOut(false, 'no_id');
header("Location: counter.php");
?>

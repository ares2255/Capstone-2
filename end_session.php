<?php
session_start();
include "config/db.php";
date_default_timezone_set('Asia/Manila');

if (isset($_GET['id'])) {
    $pc_id    = intval($_GET['id']);
    $redirect = $_GET['redirect'] ?? null;
    $end_time = date("Y-m-d H:i:s");

    // ── Duplicate prevention: check if PC is already available ──
    $check = $pdo->prepare("SELECT status FROM pcs WHERE id = :id");
    $check->execute([':id' => $pc_id]);
    $pc = $check->fetch();

    if (!$pc || $pc['status'] === 'available') {
        // Already ended — ignore duplicate request
        header("Location: counter.php");
        exit();
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
            if ($pkgRow) {
                $cost = $pkgRow['price'];
            } else {
                $cost = max($rates['minimum_charge'] ?? 0, ($total_minutes / 60) * ($rates['hourly_rate'] ?? 0));
            }
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

        if ($redirect) {
            header("Location: " . $redirect);
            exit();
        }

        header("Location: counter.php?status=ended&paid=$cost&pc=$pc_name");
        exit();
    }
}
header("Location: counter.php");
?>

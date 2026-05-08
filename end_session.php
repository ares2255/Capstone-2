<?php
session_start();
include "config/db.php";
date_default_timezone_set('Asia/Manila');

if (isset($_GET['id'])) {
    $pc_id = intval($_GET['id']);
    $redirect = $_GET['redirect'] ?? null;
    $end_time = date("Y-m-d H:i:s");

    $rates = $pdo->query("SELECT * FROM settings WHERE id = 1")->fetch();

    $stmt = $pdo->prepare("SELECT id, start_time, time_limit FROM sessions WHERE pc_id = :pc AND end_time IS NULL ORDER BY id DESC LIMIT 1");
    $stmt->execute([':pc' => $pc_id]);
    $row = $stmt->fetch();

    if ($row) {
        $session_id = $row['id'];
        $start_time = $row['start_time'];
        $time_limit = $row['time_limit'];

        $start_dt = new DateTime($start_time);
        $end_dt = new DateTime($end_time);
        $total_minutes = ($start_dt->diff($end_dt)->h * 60) + $start_dt->diff($end_dt)->i;

        $cost = 0;
        if ($time_limit == 60)       $cost = $rates['hourly_rate'] ?? 0;
        elseif ($time_limit == 180)  $cost = $rates['rate_3hr'] ?? 0;
        elseif ($time_limit == 300)  $cost = $rates['rate_5hr'] ?? 0;
        elseif ($time_limit == 420)  $cost = $rates['rate_7hr'] ?? 0;
        elseif ($time_limit == 720)  $cost = $rates['rate_12hr'] ?? 0;
        else $cost = max($rates['minimum_charge'] ?? 0, ($total_minutes / 60) * ($rates['hourly_rate'] ?? 0));

        $pc = $pdo->prepare("SELECT name FROM pcs WHERE id = :id");
        $pc->execute([':id' => $pc_id]);
        $pc_name = $pc->fetch()['name'];

        $pdo->prepare("INSERT INTO transactions (type, description, amount, time) VALUES ('Session', :desc, :amt, :t)")
            ->execute([':desc' => $pc_name, ':amt' => $cost, ':t' => $end_time]);

        $pdo->prepare("UPDATE sessions SET end_time = :et, cost = :cost WHERE id = :id")
            ->execute([':et' => $end_time, ':cost' => $cost, ':id' => $session_id]);

        $pdo->prepare("UPDATE pcs SET status = 'available' WHERE id = :id")
            ->execute([':id' => $pc_id]);

        // If customer ended their own session, redirect back to session display
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

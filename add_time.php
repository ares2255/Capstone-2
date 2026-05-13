<?php
session_start();
include "config/db.php";
date_default_timezone_set('Asia/Manila');

if (isset($_GET['id']) && isset($_GET['mins'])) {
    $pc_id   = intval($_GET['id']);
    $add_mins = intval($_GET['mins']);

    // Get the active session
    $stmt = $pdo->prepare("SELECT id, time_limit, start_time FROM sessions WHERE pc_id = :pc AND end_time IS NULL ORDER BY id DESC LIMIT 1");
    $stmt->execute([':pc' => $pc_id]);
    $row = $stmt->fetch();

    if ($row) {
        $session_id = $row['id'];

        // Calculate how many seconds have already elapsed
        $start_dt  = new DateTime($row['start_time']);
        $now_dt    = new DateTime(date("Y-m-d H:i:s"));
        $elapsed_s = $now_dt->getTimestamp() - $start_dt->getTimestamp();

        // New start_time = now, new limit = the added minutes
        // This resets the timer cleanly from this moment
        $new_start = date("Y-m-d H:i:s");

        $pdo->prepare("UPDATE sessions SET start_time = :st, time_limit = :tl WHERE id = :id")
            ->execute([':st' => $new_start, ':tl' => $add_mins, ':id' => $session_id]);

        // Look up package price and log it as a transaction
        $pkg = $pdo->prepare("SELECT price FROM packages WHERE minutes = :m LIMIT 1");
        $pkg->execute([':m' => $add_mins]);
        $pkg_row = $pkg->fetch();
        $extra_cost = $pkg_row ? $pkg_row['price'] : 0;

        if ($extra_cost > 0) {
            $pc_name_q = $pdo->prepare("SELECT name FROM pcs WHERE id = :id");
            $pc_name_q->execute([':id' => $pc_id]);
            $pc_name = $pc_name_q->fetch()['name'] ?? 'PC';

            $pdo->prepare("INSERT INTO transactions (type, description, amount, time) VALUES ('Session', :desc, :amt, :t)")
                ->execute([':desc' => $pc_name . ' (+' . $add_mins . ' min)', ':amt' => $extra_cost, ':t' => $new_start]);
        }

        header("Location: counter.php?status=extended&pc=" . urlencode($pc_id));
        exit();
    }
}
header("Location: counter.php");
?>

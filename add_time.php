<?php
session_start();
include "config/db.php";
date_default_timezone_set('Asia/Manila');

if (isset($_GET['id']) && isset($_GET['mins'])) {
    $pc_id    = intval($_GET['id']);
    $add_mins = intval($_GET['mins']);

    // Get pc name
    $pc_name_q = $pdo->prepare("SELECT name FROM pcs WHERE id = :id");
    $pc_name_q->execute([':id' => $pc_id]);
    $pc_name = $pc_name_q->fetch()['name'] ?? 'PC';

    // Get the active overtime session
    $stmt = $pdo->prepare("SELECT id, time_limit, start_time, cost FROM sessions WHERE pc_id = :pc AND end_time IS NULL ORDER BY id DESC LIMIT 1");
    $stmt->execute([':pc' => $pc_id]);
    $row = $stmt->fetch();

    if ($row) {
        $session_id = $row['id'];

        // Get previous session cost for this PC today (the overtime session already ended)
        $today = date('Y-m-d');
        $prevQ = $pdo->prepare("SELECT COALESCE(SUM(cost),0) FROM sessions WHERE pc_id=:pc AND DATE(start_time)=:d AND end_time IS NOT NULL");
        $prevQ->execute([':pc' => $pc_id, ':d' => $today]);
        $prev_cost = (float)$prevQ->fetchColumn();

        // Get new package price
        $pkg = $pdo->prepare("SELECT price FROM packages WHERE minutes = :m LIMIT 1");
        $pkg->execute([':m' => $add_mins]);
        $pkg_row = $pkg->fetch();
        $new_cost = $pkg_row ? (float)$pkg_row['price'] : 0;

        // Reset the current session: new start = now, new time_limit = added mins
        $new_start = date("Y-m-d H:i:s");
        $pdo->prepare("UPDATE sessions SET start_time=:st, time_limit=:tl, cost=:c WHERE id=:id")
            ->execute([':st' => $new_start, ':tl' => $add_mins, ':c' => $new_cost, ':id' => $session_id]);

        // Log combined transaction
        $total_cost = $prev_cost + $new_cost;
        if ($total_cost > 0) {
            $pdo->prepare("INSERT INTO transactions (type, description, amount, time) VALUES ('Session', :desc, :amt, :t)")
                ->execute([':desc' => $pc_name, ':amt' => $total_cost, ':t' => $new_start]);
        }

        header("Location: counter.php?status=extended&pc=" . urlencode($pc_id));
        exit();
    }
}
header("Location: counter.php");
?>

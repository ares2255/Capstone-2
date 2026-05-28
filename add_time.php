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

    // Get the active session
    $stmt = $pdo->prepare("SELECT id, time_limit, start_time FROM sessions WHERE pc_id = :pc AND end_time IS NULL ORDER BY id DESC LIMIT 1");
    $stmt->execute([':pc' => $pc_id]);
    $row = $stmt->fetch();

    if ($row) {
        $now = date("Y-m-d H:i:s");

        // 1. Calculate cost of the current session being closed
        $tl = (int)$row['time_limit'];
        $pkgQ = $pdo->prepare("SELECT price FROM packages WHERE minutes = :m LIMIT 1");
        $pkgQ->execute([':m' => $tl]);
        $pkgR = $pkgQ->fetch();
        $current_cost = $pkgR ? (float)$pkgR['price'] : 0;

        // 2. End the current session with its cost
        $pdo->prepare("UPDATE sessions SET end_time=:et, cost=:c WHERE id=:id AND end_time IS NULL")
            ->execute([':et' => $now, ':c' => $current_cost, ':id' => $row['id']]);

        // 3. Get new package price
        $pkgQ2 = $pdo->prepare("SELECT id, price FROM packages WHERE minutes = :m LIMIT 1");
        $pkgQ2->execute([':m' => $add_mins]);
        $pkgR2 = $pkgQ2->fetch();
        $new_cost   = $pkgR2 ? (float)$pkgR2['price'] : 0;
        $new_pkg_id = $pkgR2 ? (int)$pkgR2['id'] : null;

        // 4. Sum previous ended sessions today
        $today = date('Y-m-d');
        $sumQ = $pdo->prepare("SELECT COALESCE(SUM(cost),0) FROM sessions WHERE pc_id=:pc AND DATE(start_time)=:d AND end_time IS NOT NULL");
        $sumQ->execute([':pc' => $pc_id, ':d' => $today]);
        $total_cost = (float)$sumQ->fetchColumn() + $new_cost;

        // 5. Start a brand new session — save combined total as cost so counter shows it
        $pdo->prepare("INSERT INTO sessions (pc_id, start_time, time_limit, package_id, cost) VALUES (:pc, :st, :tl, :pkg, :c)")
            ->execute([':pc' => $pc_id, ':st' => $now, ':tl' => $add_mins, ':pkg' => $new_pkg_id, ':c' => $total_cost]);

        // 6. DELETE previous transactions for this PC today, insert one combined total
        $pdo->prepare("DELETE FROM transactions WHERE description=:desc AND DATE(time)=:d AND type='Session'")
            ->execute([':desc' => $pc_name, ':d' => $today]);

        if ($total_cost > 0) {
            $pdo->prepare("INSERT INTO transactions (type, description, amount, time) VALUES ('Session', :desc, :amt, :t)")
                ->execute([':desc' => $pc_name, ':amt' => $total_cost, ':t' => $now]);
        }

        header("Location: counter.php");
        exit();
    }
}
header("Location: counter.php");
?>

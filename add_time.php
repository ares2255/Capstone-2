<?php
session_start();
include "config/db.php";
date_default_timezone_set('Asia/Manila');

if (isset($_GET['id']) && isset($_GET['mins'])) {
    $pc_id    = intval($_GET['id']);
    $add_mins = intval($_GET['mins']);

    $pc_name_q = $pdo->prepare("SELECT name FROM pcs WHERE id = :id");
    $pc_name_q->execute([':id' => $pc_id]);
    $pc_name = $pc_name_q->fetch()['name'] ?? 'PC';

    $stmt = $pdo->prepare("SELECT id, time_limit, start_time, cost FROM sessions WHERE pc_id = :pc AND end_time IS NULL ORDER BY id DESC LIMIT 1");
    $stmt->execute([':pc' => $pc_id]);
    $row = $stmt->fetch();

    if ($row) {
        $now = date("Y-m-d H:i:s");

        // 1. Cost of the current session being closed
        $tl = (int)$row['time_limit'];
        $pkgQ = $pdo->prepare("SELECT price FROM packages WHERE minutes = :m LIMIT 1");
        $pkgQ->execute([':m' => $tl]);
        $pkgR = $pkgQ->fetch();
        $current_cost = $pkgR ? (float)$pkgR['price'] : 0;

        // 2. Previous combined total stored in current session cost
        // If current session cost > its own package price, it already has combined total
        $prev_combined = ($row['cost'] > $current_cost) ? (float)$row['cost'] - $current_cost : 0;
        // If cost equals package price, this is first add-time - prev_combined = current_cost
        if ($prev_combined == 0 && (float)$row['cost'] == $current_cost) {
            $prev_combined = $current_cost;
        }

        // 3. End the current session
        $pdo->prepare("UPDATE sessions SET end_time=:et, cost=:c WHERE id=:id AND end_time IS NULL")
            ->execute([':et' => $now, ':c' => $current_cost, ':id' => $row['id']]);

        // 4. Get new package price
        $pkgQ2 = $pdo->prepare("SELECT id, price FROM packages WHERE minutes = :m LIMIT 1");
        $pkgQ2->execute([':m' => $add_mins]);
        $pkgR2 = $pkgQ2->fetch();
        $new_cost   = $pkgR2 ? (float)$pkgR2['price'] : 0;
        $new_pkg_id = $pkgR2 ? (int)$pkgR2['id'] : null;

        // 5. Combined total = previous chain total + new package
        $total_cost = $prev_combined + $new_cost;

        // 6. Start new session with combined total as cost
        $pdo->prepare("INSERT INTO sessions (pc_id, start_time, time_limit, package_id, cost) VALUES (:pc, :st, :tl, :pkg, :c)")
            ->execute([':pc' => $pc_id, ':st' => $now, ':tl' => $add_mins, ':pkg' => $new_pkg_id, ':c' => $total_cost]);

        // 7. Log combined transaction (delete previous, insert new total)
        $today = date('Y-m-d');
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

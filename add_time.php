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

        // 2. Get the running chain total
        // If session cost is NULL = fresh session, chain total = just its package price
        // If session cost is set = already a combined total from previous add-time
        $session_cost = ($row['cost'] !== null) ? (float)$row['cost'] : $current_cost;
        $prev_combined = $session_cost; // this is the total so far including current session

        // 3. Calculate remaining time from the current session (in whole seconds, floor)
        //    so we carry it forward into the new session's time_limit.
        $remaining_secs = 0;
        if ($tl > 0 && !empty($row['start_time'])) {
            $start_dt  = new DateTime($row['start_time'], new DateTimeZone('Asia/Manila'));
            $now_dt    = new DateTime($now,               new DateTimeZone('Asia/Manila'));
            $elapsed_secs = $now_dt->getTimestamp() - $start_dt->getTimestamp();
            $total_secs   = $tl * 60;
            $remaining_secs = max(0, $total_secs - $elapsed_secs);
        }
        // Convert remaining seconds to whole minutes (round up so the customer
        // never loses a partial minute they already paid for).
        $remaining_mins = (int)ceil($remaining_secs / 60);

        // 4. End the current session with its own package cost only
        $pdo->prepare("UPDATE sessions SET end_time=:et, cost=:c WHERE id=:id AND end_time IS NULL")
            ->execute([':et' => $now, ':c' => $current_cost, ':id' => $row['id']]);

        // 5. Get new package price
        $pkgQ2 = $pdo->prepare("SELECT id, price FROM packages WHERE minutes = :m LIMIT 1");
        $pkgQ2->execute([':m' => $add_mins]);
        $pkgR2 = $pkgQ2->fetch();
        $new_cost   = $pkgR2 ? (float)$pkgR2['price'] : 0;
        $new_pkg_id = $pkgR2 ? (int)$pkgR2['id'] : null;

        // 6. Combined total = running chain total + new package
        $total_cost = $prev_combined + $new_cost;

        // 7. New time_limit = leftover minutes from current session + added package minutes
        //    e.g. 20 mins remaining + 60 mins added = 80 mins total on the new session
        $combined_mins = $remaining_mins + $add_mins;

        // 8. Start new session with combined time_limit and combined cost
        $pdo->prepare("INSERT INTO sessions (pc_id, start_time, time_limit, package_id, cost) VALUES (:pc, :st, :tl, :pkg, :c)")
            ->execute([':pc' => $pc_id, ':st' => $now, ':tl' => $combined_mins, ':pkg' => $new_pkg_id, ':c' => $total_cost]);

        // 9. Log combined transaction (delete previous, insert new total)
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

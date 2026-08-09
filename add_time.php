<?php
session_start();
include "config/db.php";
date_default_timezone_set('Asia/Manila');

if (isset($_GET['id']) && isset($_GET['mins'])) {
    $pc_id    = intval($_GET['id']);
    $add_mins = intval($_GET['mins']);

    // Get the active session
    $stmt = $pdo->prepare("SELECT id, time_limit, start_time, cost FROM sessions WHERE pc_id = :pc AND end_time IS NULL ORDER BY id DESC LIMIT 1");
    $stmt->execute([':pc' => $pc_id]);
    $row = $stmt->fetch();

    if ($row) {
        $session_id = $row['id'];
        $old_limit  = (int)($row['time_limit'] ?? 0);

        // How much has already been charged for this session?
        // If cost was already set (e.g. from a previous Add Time), use it as the running total.
        // Otherwise, look up the price of the package the session originally started with.
        if ($row['cost'] !== null) {
            $old_cost = (float)$row['cost'];
        } else {
            $old_cost = 0;
            if ($old_limit > 0) {
                $oldPkg = $pdo->prepare("SELECT price FROM packages WHERE minutes = :m LIMIT 1");
                $oldPkg->execute([':m' => $old_limit]);
                $oldPkgRow = $oldPkg->fetch();
                $old_cost = $oldPkgRow ? (float)$oldPkgRow['price'] : 0;
            }
        }

        // Price of the newly added package
        $pkg = $pdo->prepare("SELECT price FROM packages WHERE minutes = :m LIMIT 1");
        $pkg->execute([':m' => $add_mins]);
        $pkg_row = $pkg->fetch();
        $extra_cost = $pkg_row ? (float)$pkg_row['price'] : 0;

        // ── The actual fix ──
        // Do NOT touch start_time (keep counting from when the session truly began).
        // EXTEND the time_limit instead of overwriting it.
        // ADD the new package price to the running cost instead of losing the old amount.
        $new_limit = $old_limit + $add_mins;
        $new_cost  = $old_cost + $extra_cost;

        $pdo->prepare("UPDATE sessions SET time_limit = :tl, cost = :cost WHERE id = :id")
            ->execute([':tl' => $new_limit, ':cost' => $new_cost, ':id' => $session_id]);

        // Log the extra charge as a transaction
        if ($extra_cost > 0) {
            $pc_name_q = $pdo->prepare("SELECT name FROM pcs WHERE id = :id");
            $pc_name_q->execute([':id' => $pc_id]);
            $pc_name = $pc_name_q->fetch()['name'] ?? 'PC';

            $pdo->prepare("INSERT INTO transactions (type, description, amount, time) VALUES ('Session', :desc, :amt, :t)")
                ->execute([':desc' => $pc_name . ' (+' . $add_mins . ' min)', ':amt' => $extra_cost, ':t' => date("Y-m-d H:i:s")]);
        }

        header("Location: counter.php?status=extended&pc=" . urlencode($pc_id));
        exit();
    }
}
header("Location: counter.php");
?>

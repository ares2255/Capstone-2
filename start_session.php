<?php
session_start();
include "config/db.php";
date_default_timezone_set('Asia/Manila');

if (isset($_GET['id'])) {
    $pc_id = intval($_GET['id']);

    // ── Duplicate prevention: check if PC is already active ──
    $check = $pdo->prepare("SELECT status FROM pcs WHERE id = :id");
    $check->execute([':id' => $pc_id]);
    $pc = $check->fetch();

    if (!$pc || $pc['status'] === 'active') {
        // Already started — ignore duplicate request
        header("Location: counter.php");
        exit();
    }

    $time_limit = (isset($_GET['mins']) && is_numeric($_GET['mins'])) ? abs(intval($_GET['mins'])) : null;
    $start_time = date("Y-m-d H:i:s");

    $pdo->prepare("UPDATE pcs SET status = 'active' WHERE id = :id")
        ->execute([':id' => $pc_id]);

    $stmt = $pdo->prepare("INSERT INTO sessions (pc_id, start_time, time_limit) VALUES (:pc, :st, :tl)");
    $stmt->execute([':pc' => $pc_id, ':st' => $start_time, ':tl' => $time_limit]);

    header("Location: counter.php?status=started");
    exit();
}
header("Location: counter.php");
?>

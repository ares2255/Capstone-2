<?php
session_start();
include "config/db.php";
date_default_timezone_set('Asia/Manila');

if (isset($_GET['id'])) {
    $pc_id = intval($_GET['id']);

    $check = $pdo->prepare("SELECT status FROM pcs WHERE id = :id");
    $check->execute([':id' => $pc_id]);
    $pc = $check->fetch();

    if (!$pc || $pc['status'] === 'active') {
        header("Location: counter.php");
        exit();
    }

    $time_limit = (isset($_GET['mins']) && is_numeric($_GET['mins'])) ? abs(intval($_GET['mins'])) : null;
    $pkg_id     = (isset($_GET['pkg_id']) && is_numeric($_GET['pkg_id'])) ? intval($_GET['pkg_id']) : null;

    // Use the exact click timestamp sent from JS (Manila time) to avoid redirect delay
    if (!empty($_GET['ts'])) {
        $ts = $_GET['ts'];
        // Validate format: YYYY-MM-DD HH:MM:SS
        if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $ts)) {
            $start_time = $ts;
        } else {
            $start_time = date("Y-m-d H:i:s");
        }
    } else {
        $start_time = date("Y-m-d H:i:s");
    }

    $pdo->prepare("UPDATE pcs SET status = 'active' WHERE id = :id")
        ->execute([':id' => $pc_id]);

    $stmt = $pdo->prepare("INSERT INTO sessions (pc_id, start_time, time_limit, package_id) VALUES (:pc, :st, :tl, :pkg)");
    $stmt->execute([':pc' => $pc_id, ':st' => $start_time, ':tl' => $time_limit, ':pkg' => $pkg_id]);

    header("Location: counter.php?status=started");
    exit();
}
header("Location: counter.php");
?>

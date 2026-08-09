<?php
session_start();
include_once "config/db.php";
date_default_timezone_set('Asia/Manila');

if (!isset($_SESSION['username']) && !isset($_SESSION['admin_username'])) {
    header("Location: index.php"); exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // ── Server-side duplicate prevention ──
    $token = $_POST['scan_submit_token'] ?? '';
    if (empty($token)) {
        header("Location: printing.php?status=error"); exit();
    }
    if (isset($_SESSION['used_scan_tokens'][$token])) {
        header("Location: printing.php"); exit();
    }
    if (!isset($_SESSION['used_scan_tokens'])) {
        $_SESSION['used_scan_tokens'] = [];
    }
    $_SESSION['used_scan_tokens'][$token] = time();

    if (count($_SESSION['used_scan_tokens']) > 20) {
        array_shift($_SESSION['used_scan_tokens']);
    }

    // ── Process the scan job ──
    $scan_rate = 15.00; // Fixed price per page for scanning
    $pages     = intval($_POST['scan_pages']);

    if ($pages <= 0) { header("Location: printing.php?status=error"); exit(); }

    $total_price = $pages * $scan_rate;

    $stmt = $pdo->prepare("INSERT INTO print_jobs (type, pages, price, created_at) VALUES (:t, :p, :pr, :ts)");
    $stmt->execute([':t' => 'SCAN', ':p' => $pages, ':pr' => $total_price, ':ts' => date('Y-m-d H:i:s')]);

    header("Location: printing.php?status=success");
    exit();
}
header("Location: printing.php");
?>

<?php
session_start();
include_once "config/db.php";
date_default_timezone_set('Asia/Manila');

if (!isset($_SESSION['username']) && !isset($_SESSION['admin_username'])) {
    header("Location: index.php"); exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // ── Server-side duplicate prevention ──
    $token = $_POST['submit_token'] ?? '';
    if (empty($token)) {
        header("Location: printing.php?status=error"); exit();
    }
    // If this token was already used, reject it
    if (isset($_SESSION['used_print_tokens'][$token])) {
        header("Location: printing.php"); exit();
    }
    // Mark token as used immediately
    if (!isset($_SESSION['used_print_tokens'])) {
        $_SESSION['used_print_tokens'] = [];
    }
    $_SESSION['used_print_tokens'][$token] = time();

    // Keep session clean - only keep last 20 tokens
    if (count($_SESSION['used_print_tokens']) > 20) {
        array_shift($_SESSION['used_print_tokens']);
    }

    // ── Process the print job ──
    $type       = strtoupper($_POST['print_type']);
    $paper_size = isset($_POST['paper_size']) ? ucfirst(strtolower($_POST['paper_size'])) : 'Short';
    $pages      = intval($_POST['pages']);

    if ($pages <= 0) { header("Location: printing.php?status=error"); exit(); }

    $rates       = $pdo->query("SELECT bw_rate, color_rate, short_bond_rate, long_bond_rate FROM settings LIMIT 1")->fetch();
    $print_rate  = ($type === 'BW') ? floatval($rates['bw_rate']) : floatval($rates['color_rate']);
    $paper_rate  = ($paper_size === 'Long') ? floatval($rates['long_bond_rate']) : floatval($rates['short_bond_rate']);
    $total_price = $pages * ($print_rate + $paper_rate);
    $full_type   = $type . '-' . $paper_size;

    $stmt = $pdo->prepare("INSERT INTO print_jobs (type, pages, price, created_at) VALUES (:t, :p, :pr, :ts)");
    $stmt->execute([':t' => $full_type, ':p' => $pages, ':pr' => $total_price, ':ts' => date('Y-m-d H:i:s')]);

    header("Location: printing.php?status=success");
    exit();
}
header("Location: printing.php");
?>

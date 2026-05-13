<?php
session_start();
include_once "config/db.php";
date_default_timezone_set('Asia/Manila');

if (!isset($_SESSION['username']) && !isset($_SESSION['admin_username'])) {
    header("Location: index.php"); exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $type       = strtoupper($_POST['print_type']);
    $paper_size = isset($_POST['paper_size']) ? ucfirst(strtolower($_POST['paper_size'])) : 'Short';
    $pages      = intval($_POST['pages']);

    if ($pages <= 0) { header("Location: printing.php?status=error"); exit(); }

    $rates      = $pdo->query("SELECT bw_rate, color_rate, short_bond_rate, long_bond_rate FROM settings LIMIT 1")->fetch();
    $print_rate = ($type === 'BW') ? floatval($rates['bw_rate']) : floatval($rates['color_rate']);
    $paper_rate = ($paper_size === 'Long') ? floatval($rates['long_bond_rate']) : floatval($rates['short_bond_rate']);
    $unit_price = $print_rate + $paper_rate;
    $total_price = $pages * $unit_price;

    // Store type with paper size e.g. "BW-Short" or "Color-Long"
    $full_type = $type . '-' . $paper_size;

    $stmt = $pdo->prepare("INSERT INTO print_jobs (type, pages, price, created_at) VALUES (:t, :p, :pr, :ts)");
    $stmt->execute([':t' => $full_type, ':p' => $pages, ':pr' => $total_price, ':ts' => date('Y-m-d H:i:s')]);

    header("Location: printing.php?status=success");
    exit();
}
header("Location: printing.php");
?>

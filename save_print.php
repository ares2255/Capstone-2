<?php
session_start();
include_once "config/db.php";

if (!isset($_SESSION['username']) && !isset($_SESSION['admin_username'])) {
    header("Location: index.php"); exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $type = strtoupper($_POST['print_type']);
    $pages = intval($_POST['pages']);

    if ($pages <= 0) { header("Location: printing.php?status=error"); exit(); }

    $rates = $pdo->query("SELECT bw_rate, color_rate FROM settings LIMIT 1")->fetch();
    $unit_price = ($type === 'BW') ? $rates['bw_rate'] : $rates['color_rate'];
    $total_price = $pages * $unit_price;

    $stmt = $pdo->prepare("INSERT INTO print_jobs (type, pages, price, created_at) VALUES (:t, :p, :pr, NOW())");
    $stmt->execute([':t' => $type, ':p' => $pages, ':pr' => $total_price]);

    header("Location: printing.php?status=success");
    exit();
}
header("Location: printing.php");
?>

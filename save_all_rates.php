<?php
session_start();
error_reporting(0);
ini_set('display_errors', 0);
include "config/db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $stmt = $pdo->prepare("UPDATE settings SET minimum_charge=:min, bw_rate=:bw, color_rate=:col WHERE id=1");
    $stmt->execute([
        ':min' => floatval($_POST['min_charge'] ?? 0),
        ':bw'  => floatval($_POST['bw_rate']    ?? 0),
        ':col' => floatval($_POST['color_rate']  ?? 0),
    ]);
    header("Location: settings.php?status=success");
    exit();
}
header("Location: settings.php");

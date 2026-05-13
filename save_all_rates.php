<?php
session_start();
error_reporting(0);
ini_set('display_errors', 0);
include "config/db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $stmt = $pdo->prepare("UPDATE settings SET bw_rate=:bw, color_rate=:col, short_bond_rate=:short, long_bond_rate=:long WHERE id=1");
    $stmt->execute([
        ':bw'    => floatval($_POST['bw_rate']         ?? 0),
        ':col'   => floatval($_POST['color_rate']       ?? 0),
        ':short' => floatval($_POST['short_bond_rate']  ?? 0),
        ':long'  => floatval($_POST['long_bond_rate']   ?? 0),
    ]);
    header("Location: settings.php?status=success");
    exit();
}
header("Location: settings.php");

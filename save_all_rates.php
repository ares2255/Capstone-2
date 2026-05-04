<?php
session_start();
include "config/db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $stmt = $pdo->prepare("UPDATE settings SET 
        hourly_rate = :h1, rate_3hr = :h3, rate_5hr = :h5,
        rate_7hr = :h7, rate_12hr = :h12, minimum_charge = :min,
        bw_rate = :bw, color_rate = :col WHERE id = 1");

    $stmt->execute([
        ':h1'  => $_POST['hour_rate'],
        ':h3'  => $_POST['rate_3hr'],
        ':h5'  => $_POST['rate_5hr'],
        ':h7'  => $_POST['rate_7hr'],
        ':h12' => $_POST['rate_12hr'],
        ':min' => $_POST['min_charge'],
        ':bw'  => $_POST['bw_rate'],
        ':col' => $_POST['color_rate'],
    ]);

    header("Location: settings.php?status=success");
    exit();
}
?>

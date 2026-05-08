<?php
session_start();
include "config/db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $stmt = $pdo->prepare("UPDATE settings SET 
        hourly_rate=:h1, rate_2hr=:h2, rate_3hr=:h3, rate_5hr=:h5,
        rate_6hr=:h6, rate_7hr=:h7, rate_8hr=:h8, rate_9hr=:h9,
        rate_10hr=:h10, rate_11hr=:h11, rate_12hr=:h12,
        minimum_charge=:min, bw_rate=:bw, color_rate=:col
        WHERE id=1");
    $stmt->execute([
        ':h1'  => $_POST['rate_1hr'],
        ':h2'  => $_POST['rate_2hr'],
        ':h3'  => $_POST['rate_3hr'],
        ':h5'  => $_POST['rate_5hr'],
        ':h6'  => $_POST['rate_6hr'],
        ':h7'  => $_POST['rate_7hr'],
        ':h8'  => $_POST['rate_8hr'],
        ':h9'  => $_POST['rate_9hr'],
        ':h10' => $_POST['rate_10hr'],
        ':h11' => $_POST['rate_11hr'],
        ':h12' => $_POST['rate_12hr'],
        ':min' => $_POST['min_charge'],
        ':bw'  => $_POST['bw_rate'],
        ':col' => $_POST['color_rate'],
    ]);
    header("Location: settings.php?status=success");
    exit();
}

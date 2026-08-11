<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['admin_username']) && !isset($_SESSION['username'])) {
    header("Location: index.php"); exit();
}

$action = $_POST['action'] ?? '';

try {
    if ($action === 'add') {
        $hours = max(0, intval($_POST['hours'] ?? 0));
        $mins  = max(0, intval($_POST['mins'] ?? 0));
        $price = floatval($_POST['price'] ?? 0);
        $minutes = ($hours * 60) + $mins;

        if ($minutes > 0 && $price >= 0) {
            $stmt = $pdo->prepare("INSERT INTO open_time_rates (minutes, price) VALUES (:minutes, :price)");
            $stmt->execute([':minutes' => $minutes, ':price' => $price]);
        }
    } elseif ($action === 'delete') {
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0) {
            $pdo->prepare("DELETE FROM open_time_rates WHERE id = :id")->execute([':id' => $id]);
        }
    }
} catch (Exception $e) {
    // fall through to redirect either way
}

header("Location: settings.php");
exit();

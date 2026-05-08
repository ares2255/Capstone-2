<?php
session_start();
error_reporting(0);
ini_set('display_errors', 0);
include "config/db.php";

if (!isset($_SESSION['admin_username']) && !isset($_SESSION['username'])) {
    header("Location: index.php"); exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $hours = max(0, intval($_POST['hours'] ?? 0));
        $mins  = max(0, intval($_POST['mins']  ?? 0));
        $total = ($hours * 60) + $mins;
        $price = floatval($_POST['price'] ?? 0);

        if ($total > 0 && $price > 0) {
            // Build label
            if ($hours > 0 && $mins > 0)     $label = "{$hours}HR {$mins}MIN";
            elseif ($hours > 0)              $label = $hours == 1 ? "1 HR" : "{$hours} HRS";
            else                             $label = "{$mins} MIN";

            $stmt = $pdo->prepare("INSERT INTO packages (label, minutes, price) VALUES (:label, :minutes, :price)");
            $stmt->execute([':label' => $label, ':minutes' => $total, ':price' => $price]);
        }

    } elseif ($action === 'delete') {
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0) {
            $pdo->prepare("DELETE FROM packages WHERE id = :id")->execute([':id' => $id]);
        }
    }
}

header("Location: settings.php?status=success");
exit();

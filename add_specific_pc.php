<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['admin_username']) && !isset($_SESSION['username'])) {
    header("Location: index.php"); exit();
}

if (isset($_POST['add_pc'])) {
    $name = trim($_POST['pc_number'] ?? '');
    if ($name) {
        $stmt = $pdo->prepare("INSERT INTO pcs (name, status) VALUES (:n, 'available')");
        $stmt->execute([':n' => $name]);
    }
}

if (isset($_POST['delete_pc'])) {
    $stmt = $pdo->prepare("DELETE FROM pcs WHERE id = :id");
    $stmt->execute([':id' => intval($_POST['pc_id'])]);
}

if (isset($_POST['clear_all'])) {
    $pdo->exec("DELETE FROM pcs");
}

header("Location: settings.php");
exit();
?>

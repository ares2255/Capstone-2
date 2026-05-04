<?php
session_start();
include "config/db.php";

if (isset($_POST['add_pc'])) {
    $stmt = $pdo->prepare("INSERT INTO pcs (name, status) VALUES (:n, 'available')");
    $stmt->execute([':n' => $_POST['pc_number']]);
}
if (isset($_POST['delete_pc'])) {
    $stmt = $pdo->prepare("DELETE FROM pcs WHERE id = :id");
    $stmt->execute([':id' => intval($_POST['pc_id'])]);
}
if (isset($_POST['clear_all'])) {
    $pdo->query("TRUNCATE TABLE pcs");
}
header("Location: settings.php");
exit();
?>

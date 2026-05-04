<?php
session_start();
include "config/db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['pc_id'])) {
    $stmt = $pdo->prepare("DELETE FROM pcs WHERE id = :id");
    $stmt->execute([':id' => intval($_POST['pc_id'])]);
    header("Location: settings.php?status=pc_deleted");
    exit();
}
header("Location: settings.php");
?>

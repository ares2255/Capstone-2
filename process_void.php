<?php
session_start();
include "config/db.php";

if (isset($_GET['id']) && isset($_GET['type'])) {
    $id = intval($_GET['id']);
    $type = $_GET['type'];

    if ($type == 'Session') {
        $pdo->prepare("DELETE FROM sessions WHERE id = :id")->execute([':id' => $id]);
    } else {
        $pdo->prepare("DELETE FROM print_jobs WHERE id = :id")->execute([':id' => $id]);
    }
}
header("Location: dashboard.php");
?>

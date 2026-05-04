<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['admin_username']) && !isset($_SESSION['username'])) {
    header("Location: index.php"); exit();
}
if (isset($_GET['id'])) {
    $pdo->prepare("DELETE FROM print_jobs WHERE id = :id")->execute([':id' => intval($_GET['id'])]);
    header("Location: printing.php?msg=voided");
    exit();
}
header("Location: printing.php?msg=removed");
exit();
?>

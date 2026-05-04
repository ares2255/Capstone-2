<?php
include "config/db.php";

$stmt = $pdo->prepare("INSERT INTO print_jobs (type, pages, price, created_at) VALUES (:t, :p, :pr, NOW())");
$stmt->execute([':t' => $_POST['type'], ':p' => $_POST['pages'], ':pr' => $_POST['price']]);
echo "success";
?>

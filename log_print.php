<?php
include "config/db.php";
date_default_timezone_set('Asia/Manila');

$stmt = $pdo->prepare("INSERT INTO print_jobs (type, pages, price, created_at) VALUES (:t, :p, :pr, :ts)");
$stmt->execute([':t' => $_POST['type'], ':p' => $_POST['pages'], ':pr' => $_POST['price'], ':ts' => date('Y-m-d H:i:s')]);
echo "success";
?>

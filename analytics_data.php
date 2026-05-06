<?php
session_start();
include "config/db.php";
header('Content-Type: application/json');

if (!isset($_SESSION['admin_username']) && !isset($_SESSION['username'])) {
    echo json_encode(['error'=>'unauthorized']); exit();
}

$date = $_GET['date'] ?? date('Y-m-d');

$q = $pdo->prepare("SELECT COALESCE(SUM(cost),0) FROM sessions WHERE DATE(end_time)=:d");
$q->execute([':d'=>$date]); $session = $q->fetchColumn();

$q = $pdo->prepare("SELECT COALESCE(SUM(price),0) FROM print_jobs WHERE DATE(created_at)=:d");
$q->execute([':d'=>$date]); $print = $q->fetchColumn();

echo json_encode(['session'=>$session,'print'=>$print,'total'=>$session+$print]);

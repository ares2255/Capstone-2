<?php
session_start();
include "config/db.php";
header('Content-Type: application/json');

if (!isset($_SESSION['admin_username'])) {
    echo json_encode(['success'=>false,'error'=>'admin only']); exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';

try {
    if ($action === 'add') {
        $stmt = $pdo->prepare("INSERT INTO products (name, price, category, stock, emoji, active) VALUES (:n,:p,:c,:s,:e,true)");
        $stmt->execute([':n'=>$data['name'],':p'=>$data['price'],':c'=>$data['category'],':s'=>$data['stock']??99,':e'=>$data['emoji']??'🛍️']);
        echo json_encode(['success'=>true]);

    } elseif ($action === 'delete') {
        $pdo->prepare("DELETE FROM products WHERE id=:id")->execute([':id'=>$data['id']]);
        echo json_encode(['success'=>true]);

    } elseif ($action === 'toggle') {
        $pdo->prepare("UPDATE products SET active = NOT active WHERE id=:id")->execute([':id'=>$data['id']]);
        echo json_encode(['success'=>true]);
    }
} catch(PDOException $e) {
    echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
}

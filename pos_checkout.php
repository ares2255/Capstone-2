<?php
session_start();
include "config/db.php";
header('Content-Type: application/json');

if (!isset($_SESSION['admin_username']) && !isset($_SESSION['username'])) {
    echo json_encode(['success'=>false,'error'=>'unauthorized']); exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$cart = $data['cart'] ?? [];
$total = $data['total'] ?? 0;

try {
    $pdo->beginTransaction();

    // Insert POS sale record
    $stmt = $pdo->prepare("INSERT INTO pos_sales (total, created_at) VALUES (:total, NOW()) RETURNING id");
    $stmt->execute([':total' => $total]);
    $sale_id = $stmt->fetchColumn();

    // Insert each item
    $itemStmt = $pdo->prepare("INSERT INTO pos_sale_items (sale_id, product_id, qty, price) VALUES (:sid, :pid, :qty, :price)");
    $stockStmt = $pdo->prepare("UPDATE products SET stock = stock - :qty WHERE id = :id AND stock > 0");

    foreach ($cart as $item) {
        $itemStmt->execute([':sid'=>$sale_id, ':pid'=>$item['id'], ':qty'=>$item['qty'], ':price'=>$item['price']]);
        $stockStmt->execute([':qty'=>$item['qty'], ':id'=>$item['id']]);
    }

    // Insert into transactions for dashboard
    $desc = count($cart).' item(s)';
    $pdo->prepare("INSERT INTO transactions (type, description, amount, time) VALUES ('POS', :desc, :amt, NOW())")
        ->execute([':desc'=>$desc, ':amt'=>$total]);

    $pdo->commit();
    echo json_encode(['success'=>true, 'sale_id'=>$sale_id]);

} catch(PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['success'=>false, 'error'=>$e->getMessage()]);
}

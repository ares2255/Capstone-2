<?php
error_reporting(0);
ini_set('display_errors', 0);
include 'config/db.php';
header('Content-Type: application/json');

$names = [];
try {
    $stmt = $pdo->query("
        SELECT p.name
        FROM sessions s
        JOIN pcs p ON p.id = s.pc_id
        WHERE s.end_time IS NULL
          AND s.time_limit IS NOT NULL
          AND s.time_limit > 0
          AND EXTRACT(EPOCH FROM (NOW() - s.start_time))/60 > s.time_limit
        ORDER BY p.name
    ");
    $names = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    echo json_encode(['count' => 0, 'names' => [], 'error' => $e->getMessage()]);
    exit();
}

echo json_encode([
    'count' => count($names),
    'names' => $names
]);

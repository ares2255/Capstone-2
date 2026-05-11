<?php
include "config/db.php";
header('Content-Type: application/json');
header('Cache-Control: no-cache');

try {
    // PostgreSQL syntax — EXTRACT(EPOCH) gives seconds difference
    $stmt = $pdo->query("
        SELECT COUNT(*) as cnt
        FROM sessions
        WHERE end_time IS NULL
          AND time_limit IS NOT NULL
          AND EXTRACT(EPOCH FROM (NOW() AT TIME ZONE 'Asia/Manila' - start_time)) > (time_limit * 60)
    ");
    $row = $stmt->fetch();
    $count = (int)$row['cnt'];
    echo json_encode(['overtime' => $count > 0, 'count' => $count]);
} catch (Exception $e) {
    echo json_encode(['overtime' => false, 'count' => 0, 'error' => $e->getMessage()]);
}
?>

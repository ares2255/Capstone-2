<?php
include "config/db.php";
header('Content-Type: application/json');
header('Cache-Control: no-cache');

try {
    // Set session timezone to PH so NOW() is correct
    $pdo->exec("SET time_zone = '+08:00'");

    $stmt = $pdo->query("
        SELECT COUNT(*) as cnt
        FROM sessions
        WHERE end_time IS NULL
          AND time_limit IS NOT NULL
          AND TIMESTAMPDIFF(SECOND, start_time, NOW()) > (time_limit * 60)
    ");
    $row = $stmt->fetch();
    $count = (int)$row['cnt'];
    echo json_encode(['overtime' => $count > 0, 'count' => $count]);
} catch (Exception $e) {
    echo json_encode(['overtime' => false, 'count' => 0, 'error' => $e->getMessage()]);
}
?>

<?php
// Lightweight endpoint — returns whether any PC is currently in overtime
include "config/db.php";
header('Content-Type: application/json');

try {
    // Find any active session where elapsed time > time_limit (in minutes)
    $stmt = $pdo->query("
        SELECT COUNT(*) as cnt
        FROM sessions
        WHERE end_time IS NULL
          AND time_limit IS NOT NULL
          AND TIMESTAMPDIFF(SECOND, start_time, NOW()) > (time_limit * 60)
    ");
    $row = $stmt->fetch();
    echo json_encode(['overtime' => (int)$row['cnt'] > 0, 'count' => (int)$row['cnt']]);
} catch (Exception $e) {
    echo json_encode(['overtime' => false, 'count' => 0]);
}
?>

<?php
include "config/db.php";
header('Content-Type: application/json');
header('Cache-Control: no-cache');
header('Access-Control-Allow-Origin: *');

try {
    // PostgreSQL - get all active sessions with time limits
    $stmt = $pdo->query("
        SELECT id, start_time, time_limit,
               EXTRACT(EPOCH FROM (NOW() - start_time)) AS elapsed_seconds
        FROM sessions
        WHERE end_time IS NULL
          AND time_limit IS NOT NULL
    ");
    $rows = $stmt->fetchAll();
    
    $overtime_count = 0;
    foreach ($rows as $row) {
        $limit_seconds = (int)$row['time_limit'] * 60;
        $elapsed = (float)$row['elapsed_seconds'];
        if ($elapsed > $limit_seconds) {
            $overtime_count++;
        }
    }
    
    echo json_encode([
        'overtime' => $overtime_count > 0,
        'count'    => $overtime_count,
        'debug'    => count($rows) . ' active sessions checked'
    ]);
} catch (Exception $e) {
    echo json_encode(['overtime' => false, 'count' => 0, 'error' => $e->getMessage()]);
}
?>

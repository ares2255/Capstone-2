<?php
include "config/db.php";
header('Content-Type: application/json');
header('Cache-Control: no-cache');

try {
    // Only check PCs that are currently active (status='active' in pcs table)
    // then find their latest session and check if it's overtime
    $stmt = $pdo->query("
        SELECT s.id, s.start_time, s.time_limit,
               EXTRACT(EPOCH FROM (NOW() - s.start_time)) AS elapsed_seconds
        FROM sessions s
        INNER JOIN pcs p ON p.id = s.pc_id
        WHERE p.status = 'active'
          AND s.end_time IS NULL
          AND s.time_limit IS NOT NULL
    ");
    $rows = $stmt->fetchAll();

    $overtime_count = 0;
    foreach ($rows as $row) {
        if ((float)$row['elapsed_seconds'] > (int)$row['time_limit'] * 60) {
            $overtime_count++;
        }
    }

    echo json_encode([
        'overtime' => $overtime_count > 0,
        'count'    => $overtime_count
    ]);
} catch (Exception $e) {
    echo json_encode(['overtime' => false, 'count' => 0, 'error' => $e->getMessage()]);
}
?>

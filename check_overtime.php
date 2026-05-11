<?php
error_reporting(0);
ini_set('display_errors', 0);
include 'config/db.php';
header('Content-Type: application/json');
header('Cache-Control: no-cache');

date_default_timezone_set('Asia/Manila');

$names = [];
try {
    // Only check PCs that are currently active + session not ended
    $stmt = $pdo->query("
        SELECT DISTINCT p.name, s.start_time, s.time_limit
        FROM sessions s
        JOIN pcs p ON p.id = s.pc_id
        WHERE s.end_time IS NULL
          AND p.status = 'active'
          AND s.time_limit IS NOT NULL
          AND s.time_limit > 0
          AND s.id = (
              SELECT MAX(s2.id) FROM sessions s2 WHERE s2.pc_id = s.pc_id AND s2.end_time IS NULL
          )
    ");
    $rows = $stmt->fetchAll();
    $now = time();
    foreach ($rows as $row) {
        $start = strtotime($row['start_time']);
        if (!$start) continue;
        $elapsed_mins = ($now - $start) / 60;
        if ($elapsed_mins > (float)$row['time_limit']) {
            $names[] = $row['name'];
        }
    }
} catch (Exception $e) {
    echo json_encode(['count' => 0, 'names' => [], 'error' => $e->getMessage()]);
    exit();
}

echo json_encode(['count' => count($names), 'names' => $names]);

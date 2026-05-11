<?php
error_reporting(0);
ini_set('display_errors', 0);
include 'config/db.php';
header('Content-Type: application/json');
header('Cache-Control: no-cache');

date_default_timezone_set('Asia/Manila');

$names = [];
try {
    $stmt = $pdo->query("
        SELECT p.name, s.start_time, s.time_limit
        FROM sessions s
        JOIN pcs p ON p.id = s.pc_id
        WHERE s.end_time IS NULL
          AND s.time_limit IS NOT NULL
          AND s.time_limit > 0
    ");
    $rows = $stmt->fetchAll();
    $now = time();
    foreach ($rows as $row) {
        // Try multiple timestamp formats
        $start = strtotime($row['start_time']);
        if (!$start) $start = strtotime(str_replace(' ', 'T', $row['start_time']));
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

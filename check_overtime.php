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
        SELECT DISTINCT ON (p.id) p.name, s.start_time, s.time_limit
        FROM sessions s
        JOIN pcs p ON p.id = s.pc_id
        WHERE s.end_time IS NULL
          AND p.status = 'active'
          AND s.time_limit IS NOT NULL
          AND s.time_limit > 0
        ORDER BY p.id, s.id DESC
    ");
    $rows = $stmt->fetchAll();
    foreach ($rows as $row) {
        $time_limit = (int)$row['time_limit'];
        if ($time_limit <= 0) continue;
        // Parse start_time as Manila time explicitly to avoid UTC misread
        $start_dt = new DateTime($row['start_time'], new DateTimeZone('Asia/Manila'));
        $now_dt   = new DateTime('now', new DateTimeZone('Asia/Manila'));
        $elapsed_secs = $now_dt->getTimestamp() - $start_dt->getTimestamp();
        $elapsed_mins = $elapsed_secs / 60;
        if ($elapsed_mins > $time_limit) {
            $names[] = $row['name'];
        }
    }
} catch (Exception $e) {
    echo json_encode(['count'=>0,'names'=>[],'error'=>$e->getMessage()]);
    exit();
}
echo json_encode(['count'=>count($names),'names'=>$names]);

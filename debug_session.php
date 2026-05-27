<?php
session_start();
include 'config/db.php';
date_default_timezone_set('Asia/Manila');
if (!isset($_SESSION['admin_username'])) { header('Location: index.php'); exit(); }

$rows = $pdo->query("
    SELECT s.id, p.name as pc, s.start_time, s.end_time, s.time_limit, s.package_id, s.cost,
           EXTRACT(EPOCH FROM (s.end_time - s.start_time)) as elapsed_secs
    FROM sessions s
    JOIN pcs p ON p.id = s.pc_id
    WHERE s.end_time IS NOT NULL
    ORDER BY s.id DESC
    LIMIT 3
")->fetchAll();

echo "<pre style='font-family:monospace;font-size:14px;background:#111;color:#0f0;padding:20px'>";
foreach ($rows as $r) {
    $secs = (int)$r['elapsed_secs'];
    echo "PC: {$r['pc']}  | session_id: {$r['id']}\n";
    echo "  start_time  : {$r['start_time']}\n";
    echo "  end_time    : {$r['end_time']}\n";
    echo "  elapsed_sec : $secs  (" . floor($secs/60) . "m " . ($secs%60) . "s)\n";
    echo "  time_limit  : " . var_export($r['time_limit'], true) . "\n";
    echo "  cost_saved  : {$r['cost']}\n\n";
}

// Also show server time right now
echo "Server NOW : " . date('Y-m-d H:i:s') . "\n";
echo "</pre>";
?>

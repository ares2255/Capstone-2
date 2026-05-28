<?php
session_start();
include 'config/db.php';
date_default_timezone_set('Asia/Manila');
if (!isset($_SESSION['admin_username'])) { header('Location: index.php'); exit(); }

$rows = $pdo->query("
    SELECT s.id, p.name, s.start_time, s.end_time, s.time_limit, s.package_id, s.cost
    FROM sessions s JOIN pcs p ON p.id = s.pc_id
    WHERE p.name = 'PC-01'
    ORDER BY s.id DESC LIMIT 5
")->fetchAll();

echo "<pre style='background:#111;color:#0f0;padding:20px'>";
foreach ($rows as $r) {
    echo "id:{$r['id']} | start:{$r['start_time']} | end:{$r['end_time']}\n";
    echo "  time_limit: " . var_export($r['time_limit'], true) . "\n";
    echo "  cost: {$r['cost']}\n\n";
}
echo "</pre>";

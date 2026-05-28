<?php
session_start();
include 'config/db.php';
date_default_timezone_set('Asia/Manila');
if (!isset($_SESSION['admin_username'])) { header('Location: index.php'); exit(); }

// Show today's transactions
$rows = $pdo->query("
    SELECT * FROM transactions
    WHERE DATE(time) = CURRENT_DATE
    ORDER BY id DESC LIMIT 10
")->fetchAll();

echo "<pre style='background:#111;color:#0f0;padding:20px'>";
echo "Today's transactions:\n\n";
foreach ($rows as $r) {
    echo "id:{$r['id']} | {$r['type']} | {$r['description']} | ₱{$r['amount']} | {$r['time']}\n";
}

// Show today's sessions for PC-01
echo "\n\nPC-01 sessions today:\n\n";
$rows2 = $pdo->query("
    SELECT s.id, s.start_time, s.end_time, s.time_limit, s.cost
    FROM sessions s JOIN pcs p ON p.id = s.pc_id
    WHERE p.name='PC-01' AND DATE(s.start_time)=CURRENT_DATE
    ORDER BY s.id DESC
")->fetchAll();
foreach ($rows2 as $r) {
    echo "id:{$r['id']} | tl:{$r['time_limit']} | cost:{$r['cost']} | end:" . ($r['end_time'] ? 'YES' : 'NO') . "\n";
}
echo "</pre>";

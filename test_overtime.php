<?php
include 'config/db.php';
date_default_timezone_set('Asia/Manila');
header('Content-Type: text/plain');

$rows = $pdo->query("
    SELECT p.name, s.start_time, s.time_limit
    FROM sessions s JOIN pcs p ON p.id = s.pc_id
    WHERE s.end_time IS NULL AND s.time_limit > 0
    ORDER BY s.id DESC LIMIT 5
")->fetchAll();

echo "Server time: " . date('Y-m-d H:i:s') . "\n\n";
foreach ($rows as $r) {
    $start_dt = new DateTime($r['start_time'], new DateTimeZone('Asia/Manila'));
    $now_dt = new DateTime('now', new DateTimeZone('Asia/Manila'));
    $secs = $now_dt->getTimestamp() - $start_dt->getTimestamp();
    $old_secs = time() - strtotime($r['start_time']); // old buggy way
    echo "PC: {$r['name']} | limit: {$r['time_limit']}min\n";
    echo "  start_time  : {$r['start_time']}\n";
    echo "  correct secs: $secs (" . round($secs/60,1) . " min)\n";
    echo "  buggy secs  : $old_secs (" . round($old_secs/60,1) . " min)\n";
    echo "  overtime?   : " . ($secs/60 > $r['time_limit'] ? 'YES' : 'NO') . "\n\n";
}

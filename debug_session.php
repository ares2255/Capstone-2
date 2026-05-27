<?php
session_start();
include 'config/db.php';
date_default_timezone_set('Asia/Manila');
if (!isset($_SESSION['admin_username'])) { header('Location: index.php'); exit(); }

// Show last 5 ended sessions with all relevant fields
$rows = $pdo->query("
    SELECT s.id, p.name as pc, s.start_time, s.end_time, s.time_limit, s.package_id, s.cost,
           EXTRACT(EPOCH FROM (s.end_time - s.start_time)) as elapsed_secs
    FROM sessions s
    JOIN pcs p ON p.id = s.pc_id
    WHERE s.end_time IS NOT NULL
    ORDER BY s.id DESC
    LIMIT 5
")->fetchAll();

echo "<pre style='font-family:monospace;font-size:14px;background:#111;color:#0f0;padding:20px'>";
echo "Last 5 ended sessions:\n\n";
foreach ($rows as $r) {
    $secs = (int)$r['elapsed_secs'];
    $mins_floor = floor($secs / 60);
    echo "PC: {$r['pc']}  | session_id: {$r['id']}\n";
    echo "  start_time : {$r['start_time']}\n";
    echo "  end_time   : {$r['end_time']}\n";
    echo "  time_limit : " . var_export($r['time_limit'], true) . "\n";
    echo "  package_id : " . var_export($r['package_id'], true) . "\n";
    echo "  elapsed_sec: $secs\n";
    echo "  mins_floor : $mins_floor\n";
    echo "  cost_saved : {$r['cost']}\n\n";
}

// Show packages
echo "\nPackages:\n";
$pkgs = $pdo->query("SELECT * FROM packages ORDER BY minutes ASC")->fetchAll();
foreach ($pkgs as $pk) {
    echo "  id:{$pk['id']}  minutes:{$pk['minutes']}  price:{$pk['price']}\n";
}
echo "</pre>";
?>

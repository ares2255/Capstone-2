<?php
// TEMPORARY DEBUG FILE — delete after fixing
session_start();
include "config/db.php";
date_default_timezone_set('Asia/Manila');
header('Content-Type: text/plain');

echo "=== DEBUG END SESSION ===\n\n";

// Show all PCs that are still 'active'
$pcs = $pdo->query("SELECT id, name, status FROM pcs WHERE status = 'active'")->fetchAll();
echo "Active PCs in DB:\n";
if (empty($pcs)) {
    echo "  (none — all PCs are available)\n";
} else {
    foreach ($pcs as $p) {
        echo "  ID={$p['id']} Name={$p['name']} Status={$p['status']}\n";
    }
}

echo "\nSessions with no end_time:\n";
$sess = $pdo->query("SELECT s.id, s.pc_id, p.name, s.start_time, s.time_limit FROM sessions s JOIN pcs p ON p.id=s.pc_id WHERE s.end_time IS NULL ORDER BY s.id DESC LIMIT 10")->fetchAll();
if (empty($sess)) {
    echo "  (none)\n";
} else {
    foreach ($sess as $s) {
        $elapsed = round((time() - strtotime($s['start_time'])) / 60, 1);
        echo "  SessionID={$s['id']} PC={$s['name']} start={$s['start_time']} limit={$s['time_limit']}min elapsed={$elapsed}min\n";
    }
}

// If ?fix=1 passed, force-end all orphaned sessions
if (isset($_GET['fix'])) {
    echo "\n=== FORCE FIXING ALL ORPHANED SESSIONS ===\n";
    foreach ($sess as $s) {
        $pdo->prepare("UPDATE sessions SET end_time = NOW(), cost = 0 WHERE id = :id AND end_time IS NULL")
            ->execute([':id' => $s['id']]);
        $pdo->prepare("UPDATE pcs SET status = 'available' WHERE id = :id")
            ->execute([':id' => $s['pc_id']]);
        echo "  Fixed: {$s['name']} (session {$s['id']})\n";
    }
    echo "\nDone! Reload counter.php now.\n";
}

if (!isset($_GET['fix'])) {
    echo "\nTo force-fix all stuck sessions, open: debug_end.php?fix=1\n";
}
?>

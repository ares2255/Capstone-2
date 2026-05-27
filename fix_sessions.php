<?php
// ONE-TIME FIX: Ends all broken active sessions so you can start fresh
// DELETE THIS FILE after running it!
include 'config/db.php';

try {
    // Close all open sessions
    $pdo->exec("UPDATE sessions SET end_time = NOW(), cost = 0 WHERE end_time IS NULL");
    // Reset all PCs to available
    $pdo->exec("UPDATE pcs SET status = 'available'");
    echo "✅ All sessions cleared. All PCs reset to AVAILABLE. Delete this file now!";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>

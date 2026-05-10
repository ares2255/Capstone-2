<?php
// TEMPORARY DEBUG FILE — DELETE AFTER FIXING
include "config/db.php";
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
header('Content-Type: application/json');

$results = [];

// 1. Show all users (id, username, role) — NO passwords
try {
    $stmt = $pdo->query("SELECT id, username, role FROM users");
    $results['users'] = $stmt->fetchAll();
} catch (Exception $e) {
    $results['users_error'] = $e->getMessage();
}

// 2. Show reset_codes table
try {
    $stmt = $pdo->query("SELECT user_id, expires_at, (expires_at < NOW()) as expired FROM reset_codes");
    $results['reset_codes'] = $stmt->fetchAll();
} catch (Exception $e) {
    $results['reset_codes_error'] = $e->getMessage();
}

// 3. Check if users table has email column
try {
    $stmt = $pdo->query("SELECT id, username, email FROM users LIMIT 5");
    $results['users_with_email'] = $stmt->fetchAll();
} catch (Exception $e) {
    $results['email_column_error'] = $e->getMessage();
}

echo json_encode($results, JSON_PRETTY_PRINT);

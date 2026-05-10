<?php
error_reporting(0);
ini_set('display_errors', 0);
include "config/db.php";
date_default_timezone_set('Asia/Manila');
header('Content-Type: application/json');

$input = trim($_POST['username'] ?? '');
if (!$input) {
    echo json_encode(['success' => false, 'error' => 'Please enter your username or email.']);
    exit();
}

// Search by username OR email (case-insensitive)
$stmt = $pdo->prepare("SELECT id, email, username FROM users WHERE LOWER(username) = LOWER(:u) OR LOWER(email) = LOWER(:e)");
$stmt->execute([':u' => $input, ':e' => $input]);
$user = $stmt->fetch();

if (!$user) {
    echo json_encode(['success' => false, 'error' => '❌ No account found. You entered: ' . htmlspecialchars($input)]);
    exit();
}

if (empty($user['email'])) {
    echo json_encode(['success' => false, 'error' => '❌ No email linked to this account. Contact your administrator.']);
    exit();
}

// Generate 6-digit reset code
$code        = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
$hashed_code = password_hash($code, PASSWORD_DEFAULT);
$expires_at  = date('Y-m-d H:i:s', strtotime('+15 minutes'));

// Ensure reset_codes table exists, then upsert
$pdo->exec("CREATE TABLE IF NOT EXISTS reset_codes (
    user_id     INT PRIMARY KEY,
    code_hash   VARCHAR(255) NOT NULL,
    expires_at  TIMESTAMP NOT NULL
)");

// Delete any existing code for this user, then insert fresh
$pdo->prepare("DELETE FROM reset_codes WHERE user_id = :id")->execute([':id' => $user['id']]);
$pdo->prepare("INSERT INTO reset_codes (user_id, code_hash, expires_at) VALUES (:id, :hash, :exp)")
    ->execute([':id' => $user['id'], ':hash' => $hashed_code, ':exp' => $expires_at]);

echo json_encode([
    'success'  => true,
    'email'    => $user['email'],
    'username' => $user['username'],
    'code'     => $code          // sent via EmailJS to the user's email
]);

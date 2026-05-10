<?php
error_reporting(0);
ini_set('display_errors', 0);
include "config/db.php";
header('Content-Type: application/json');

$input = trim($_POST['username'] ?? '');
if (!$input) {
    echo json_encode(['success' => false, 'error' => 'Please enter your username or email. [v2]']);
    exit();
}

// Search by username OR email
$stmt = $pdo->prepare("SELECT id, email, username FROM users WHERE username = :u OR email = :e");
$stmt->execute([':u' => $input, ':e' => $input]);
$user = $stmt->fetch();

if (!$user) {
    echo json_encode(['success' => false, 'error' => '❌ No account found with that username or email. [v2]']);
    exit();
}

if (empty($user['email'])) {
    echo json_encode(['success' => false, 'error' => '❌ No email linked to this account. Contact your administrator.']);
    exit();
}

// Generate temp password
$words  = ['Blue','Red','Fast','Star','Moon','Fire','Sky','Rock','Bolt','Wave'];
$temp   = $words[array_rand($words)] . rand(100, 999);
$hashed = password_hash($temp, PASSWORD_DEFAULT);

$pdo->prepare("UPDATE users SET password = :p WHERE id = :id")
    ->execute([':p' => $hashed, ':id' => $user['id']]);

echo json_encode([
    'success'   => true,
    'email'     => $user['email'],
    'username'  => $user['username'],
    'temp_pass' => $temp
]);

<?php
include "config/db.php";
header('Content-Type: application/json');

$username = trim($_POST['username'] ?? '');
if (!$username) {
    echo json_encode(['success' => false, 'error' => 'Please enter your username.']);
    exit();
}

$stmt = $pdo->prepare("SELECT id, email, username FROM users WHERE username = :u");
$stmt->execute([':u' => $username]);
$user = $stmt->fetch();

if (!$user) {
    echo json_encode(['success' => false, 'error' => '❌ Username not found.']);
    exit();
}

if (empty($user['email'])) {
    echo json_encode(['success' => false, 'error' => '❌ No email address linked to this account. Please contact your administrator.']);
    exit();
}

// Generate temp password
$words = ['Blue','Red','Fast','Star','Moon','Fire','Sky','Rock','Bolt','Wave'];
$temp  = $words[array_rand($words)] . rand(100, 999);
$hashed = password_hash($temp, PASSWORD_DEFAULT);

$pdo->prepare("UPDATE users SET password = :p WHERE username = :u")
    ->execute([':p' => $hashed, ':u' => $username]);

echo json_encode([
    'success'   => true,
    'email'     => $user['email'],
    'username'  => $user['username'],
    'temp_pass' => $temp
]);

<?php
include "config/db.php";
header('Content-Type: application/json');

$username = $_POST['username'] ?? '';

if (!$username) {
    echo json_encode(['success' => false, 'error' => 'Please enter a username.']);
    exit();
}

$stmt = $pdo->prepare("SELECT id FROM users WHERE username = :u");
$stmt->execute([':u' => $username]);
$user = $stmt->fetch();

if (!$user) {
    echo json_encode(['success' => false, 'error' => '❌ Username not found. Check the spelling and try again.']);
    exit();
}

// Generate a simple memorable temp password
$words  = ['Blue','Red','Fast','Star','Moon','Fire','Sky','Rock'];
$temp   = $words[array_rand($words)] . rand(100, 999);
$hashed = password_hash($temp, PASSWORD_DEFAULT);

$pdo->prepare("UPDATE users SET password = :p WHERE username = :u")
    ->execute([':p' => $hashed, ':u' => $username]);

echo json_encode(['success' => true, 'temp_pass' => $temp]);

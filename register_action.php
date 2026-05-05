<?php
session_start();
include "config/db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: register.php");
    exit();
}

$user = trim($_POST['reg_user'] ?? '');
$pass = $_POST['reg_pass'] ?? '';

if (empty($user) || empty($pass)) {
    header("Location: register.php?error=empty");
    exit();
}

// Using 'users' table (matches your Supabase setup)
$stmt = $pdo->prepare("SELECT id FROM users WHERE username = :u");
$stmt->execute([':u' => $user]);

if ($stmt->fetch()) {
    header("Location: register.php?error=exists");
    exit();
}

$hashed = password_hash($pass, PASSWORD_DEFAULT);
$insert = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (:u, :p, 'admin')");
if ($insert->execute([':u' => $user, ':p' => $hashed])) {
    header("Location: admin_login.php?status=registered");
    exit();
} else {
    echo "Error creating account. Please try again.";
}
?>

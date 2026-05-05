<?php
session_start();
include "config/db.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: admin_login.php");
    exit();
}

$user = trim($_POST['admin_user'] ?? '');
$pass = $_POST['admin_pass'] ?? '';

// Using 'users' table (matches your Supabase setup)
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = :u LIMIT 1");
$stmt->execute([':u' => $user]);
$admin = $stmt->fetch();

if ($admin && password_verify($pass, $admin['password'])) {
    $_SESSION['admin_username'] = $admin['username'];
    $_SESSION['role'] = $admin['role'];
    header("Location: dashboard.php");
} else {
    header("Location: admin_login.php?error=1");
}
exit();
?>

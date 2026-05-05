<?php
session_start();
include "config/db.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: admin_login.php");
    exit();
}

$user = trim($_POST['admin_user'] ?? '');
$pass = $_POST['admin_pass'] ?? '';

// Use PDO (PostgreSQL - no $conn)
$stmt = $pdo->prepare("SELECT * FROM admins WHERE username = :u LIMIT 1");
$stmt->execute([':u' => $user]);
$admin = $stmt->fetch();

if ($admin && password_verify($pass, $admin['password'])) {
    $_SESSION['admin_username'] = $admin['username'];
    header("Location: dashboard.php");
} else {
    header("Location: admin_login.php?error=1");
}
exit();
?>

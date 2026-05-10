<?php
session_start();
include "config/db.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: admin_login.php");
    exit();
}

$user = trim($_POST['admin_user'] ?? '');
$pass = $_POST['admin_pass'] ?? '';

// Match by username OR email (case-insensitive)
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE LOWER(username) = LOWER(:u) OR LOWER(email) = LOWER(:e) LIMIT 1");
    $stmt->execute([':u' => $user, ':e' => $user]);
    $admin = $stmt->fetch();
} catch (Exception $e) {
    // email column may not exist — fall back to username only
    $stmt = $pdo->prepare("SELECT * FROM users WHERE LOWER(username) = LOWER(:u) LIMIT 1");
    $stmt->execute([':u' => $user]);
    $admin = $stmt->fetch();
}

if ($admin && password_verify($pass, $admin['password'])) {
    $_SESSION['admin_username'] = $admin['username'];
    $_SESSION['role'] = $admin['role'];
    header("Location: dashboard.php");
} else {
    header("Location: admin_login.php?error=1");
}
exit();
?>

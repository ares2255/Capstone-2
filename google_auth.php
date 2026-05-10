<?php
session_start();
include "config/db.php";

$credential = $_POST['credential'] ?? '';
$action     = $_POST['action'] ?? 'login';

if (!$credential) {
    header("Location: admin_login.php?status=google_fail"); exit();
}

// Decode JWT (no need for signature verification for basic use)
$parts = explode('.', $credential);
if (count($parts) !== 3) {
    header("Location: admin_login.php?status=google_fail"); exit();
}
$payload = json_decode(base64_decode(str_pad(strtr($parts[1], '-_', '+/'), strlen($parts[1]) % 4, '=', STR_PAD_RIGHT)), true);

$google_id    = $payload['sub']   ?? '';
$google_email = $payload['email'] ?? '';
$google_name  = $payload['name']  ?? $google_email;

if (!$google_email) {
    header("Location: admin_login.php?status=google_fail"); exit();
}

if ($action === 'register') {
    $check = $pdo->prepare("SELECT id FROM users WHERE google_id=:gid OR username=:email");
    $check->execute([':gid' => $google_id, ':email' => $google_email]);
    if ($check->fetch()) {
        header("Location: register.php?error=google_exists"); exit();
    }
    $stmt = $pdo->prepare("INSERT INTO users (username, password, role, google_id) VALUES (:u, :p, 'admin', :gid)");
    $stmt->execute([':u' => $google_name, ':p' => password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT), ':gid' => $google_id]);
    header("Location: admin_login.php?status=registered"); exit();

} else {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE google_id=:gid OR username=:email");
    $stmt->execute([':gid' => $google_id, ':email' => $google_email]);
    $user = $stmt->fetch();

    if ($user) {
        $_SESSION['admin_username'] = $user['username'];
        $_SESSION['role']           = $user['role'];
        header("Location: dashboard.php"); exit();
    } else {
        header("Location: admin_login.php?status=google_fail"); exit();
    }
}

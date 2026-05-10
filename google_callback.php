<?php
session_start();
error_reporting(0);
ini_set('display_errors', 0);
include "config/db.php";

$GOOGLE_CLIENT_ID     = '647107465413-18hemskapc88e4gil1a9g009qpll9074.apps.googleusercontent.com';
$GOOGLE_CLIENT_SECRET = getenv('GOOGLE_CLIENT_SECRET') ?: '';
$GOOGLE_REDIRECT_URI  = 'https://capstone-2-production-c904.up.railway.app/google_callback.php';

$code  = $_GET['code']  ?? '';
$state = $_GET['state'] ?? 'login'; // 'login' or 'register'
$error = $_GET['error'] ?? '';

if ($error || !$code) {
    header("Location: admin_login.php?status=google_fail"); exit();
}

// Exchange code for access token
$tokenResp = file_get_contents('https://oauth2.googleapis.com/token', false, stream_context_create([
    'http' => [
        'method'  => 'POST',
        'header'  => 'Content-Type: application/x-www-form-urlencoded',
        'content' => http_build_query([
            'code'          => $code,
            'client_id'     => $GOOGLE_CLIENT_ID,
            'client_secret' => $GOOGLE_CLIENT_SECRET,
            'redirect_uri'  => $GOOGLE_REDIRECT_URI,
            'grant_type'    => 'authorization_code',
        ])
    ]
]));

if (!$tokenResp) {
    header("Location: admin_login.php?status=google_fail"); exit();
}

$token = json_decode($tokenResp, true);
$accessToken = $token['access_token'] ?? '';

if (!$accessToken) {
    header("Location: admin_login.php?status=google_fail"); exit();
}

// Get user info from Google
$userInfo = file_get_contents('https://www.googleapis.com/oauth2/v2/userinfo', false, stream_context_create([
    'http' => [
        'header' => 'Authorization: Bearer ' . $accessToken
    ]
]));

if (!$userInfo) {
    header("Location: admin_login.php?status=google_fail"); exit();
}

$user = json_decode($userInfo, true);
$google_id    = $user['id']    ?? '';
$google_email = $user['email'] ?? '';
$google_name  = $user['name']  ?? $google_email;

if (!$google_email) {
    header("Location: admin_login.php?status=google_fail"); exit();
}

if ($state === 'register') {
    // Check if already registered
    $check = $pdo->prepare("SELECT id FROM users WHERE google_id=:gid OR email=:email OR username=:name");
    $check->execute([':gid' => $google_id, ':email' => $google_email, ':name' => $google_name]);
    if ($check->fetch()) {
        header("Location: register.php?error=google_exists"); exit();
    }
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role, google_id) VALUES (:u, :e, :p, 'admin', :gid)");
    $stmt->execute([
        ':u'   => $google_name,
        ':e'   => $google_email,
        ':p'   => password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT),
        ':gid' => $google_id
    ]);
    header("Location: admin_login.php?status=registered"); exit();

} else {
    // Login
    $stmt = $pdo->prepare("SELECT * FROM users WHERE google_id=:gid OR email=:email");
    $stmt->execute([':gid' => $google_id, ':email' => $google_email]);
    $dbUser = $stmt->fetch();

    if ($dbUser) {
        // Update google_id if not set
        if (empty($dbUser['google_id'])) {
            $pdo->prepare("UPDATE users SET google_id=:gid WHERE id=:id")
                ->execute([':gid' => $google_id, ':id' => $dbUser['id']]);
        }
        $_SESSION['admin_username'] = $dbUser['username'];
        $_SESSION['role']           = $dbUser['role'];
        header("Location: dashboard.php"); exit();
    } else {
        header("Location: admin_login.php?status=google_fail"); exit();
    }
}

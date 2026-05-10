<?php
error_reporting(0);
ini_set('display_errors', 0);
include "config/db.php";
header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

// ── STEP 1: Verify the 6-digit code ──────────────────────────────────────────
if ($action === 'verify_code') {
    $input = trim($_POST['username'] ?? '');
    $code  = trim($_POST['code'] ?? '');

    if (!$input || !$code) {
        echo json_encode(['success' => false, 'error' => 'Username/email and code are required.']);
        exit();
    }

    // Find user
    $stmt = $pdo->prepare("SELECT id FROM users WHERE LOWER(username) = LOWER(:u) OR LOWER(email) = LOWER(:e)");
    $stmt->execute([':u' => $input, ':e' => $input]);
    $user = $stmt->fetch();

    if (!$user) {
        echo json_encode(['success' => false, 'error' => '❌ Account not found.']);
        exit();
    }

    // Find unexpired code
    $stmt = $pdo->prepare("SELECT code_hash, expires_at FROM reset_codes WHERE user_id = :id");
    $stmt->execute([':id' => $user['id']]);
    $row = $stmt->fetch();

    if (!$row) {
        echo json_encode(['success' => false, 'error' => '❌ No reset code found. Please request a new one.']);
        exit();
    }

    if (strtotime($row['expires_at']) < time()) {
        echo json_encode(['success' => false, 'error' => '❌ Code has expired. Please request a new one.']);
        exit();
    }

    if (!password_verify($code, $row['code_hash'])) {
        echo json_encode(['success' => false, 'error' => '❌ Incorrect code. Please try again.']);
        exit();
    }

    // Code is valid — return a short-lived token so the next step knows it's verified
    $token = bin2hex(random_bytes(16));
    $token_hash = password_hash($token, PASSWORD_DEFAULT);
    $token_exp  = date('Y-m-d H:i:s', strtotime('+5 minutes'));

    // Reuse the reset_codes row to store the verified token
    $pdo->prepare("UPDATE reset_codes SET code_hash = :hash, expires_at = :exp WHERE user_id = :id")
        ->execute([':hash' => $token_hash, ':exp' => $token_exp, ':id' => $user['id']]);

    echo json_encode(['success' => true, 'user_id' => $user['id'], 'token' => $token]);
    exit();
}

// ── STEP 2: Set the new password ─────────────────────────────────────────────
if ($action === 'set_password') {
    $user_id     = (int)($_POST['user_id'] ?? 0);
    $token       = trim($_POST['token'] ?? '');
    $new_pass    = $_POST['new_password'] ?? '';
    $confirm     = $_POST['confirm_password'] ?? '';

    if (!$user_id || !$token || !$new_pass) {
        echo json_encode(['success' => false, 'error' => 'Missing required fields.']);
        exit();
    }

    if ($new_pass !== $confirm) {
        echo json_encode(['success' => false, 'error' => '❌ Passwords do not match.']);
        exit();
    }

    if (strlen($new_pass) < 6) {
        echo json_encode(['success' => false, 'error' => '❌ Password must be at least 6 characters.']);
        exit();
    }

    // Validate token
    $stmt = $pdo->prepare("SELECT code_hash, expires_at FROM reset_codes WHERE user_id = :id");
    $stmt->execute([':id' => $user_id]);
    $row = $stmt->fetch();

    if (!$row || strtotime($row['expires_at']) < time() || !password_verify($token, $row['code_hash'])) {
        echo json_encode(['success' => false, 'error' => '❌ Session expired. Please start over.']);
        exit();
    }

    // Update password
    $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
    $pdo->prepare("UPDATE users SET password = :p WHERE id = :id")
        ->execute([':p' => $hashed, ':id' => $user_id]);

    // Clean up the code
    $pdo->prepare("DELETE FROM reset_codes WHERE user_id = :id")->execute([':id' => $user_id]);

    echo json_encode(['success' => true]);
    exit();
}

echo json_encode(['success' => false, 'error' => 'Invalid action.']);

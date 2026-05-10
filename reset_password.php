<?php
error_reporting(0);
ini_set('display_errors', 0);
include "config/db.php";
header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

// ── STEP 1: Verify the 6-digit code AND immediately set new password ──────────
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

    // Find code row
    $stmt = $pdo->prepare("SELECT code_hash, expires_at FROM reset_codes WHERE user_id = :id");
    $stmt->execute([':id' => $user['id']]);
    $row = $stmt->fetch();

    if (!$row) {
        echo json_encode(['success' => false, 'error' => '❌ No reset code found. Please request a new one.']);
        exit();
    }

    // Compare expiry using database time to avoid timezone issues
    $expCheck = $pdo->prepare("SELECT (expires_at < NOW()) AS expired FROM reset_codes WHERE user_id = :id");
    $expCheck->execute([':id' => $user['id']]);
    $expRow = $expCheck->fetch();
    if ($expRow && $expRow['expired']) {
        echo json_encode(['success' => false, 'error' => '❌ Code has expired. Please request a new one.']);
        exit();
    }

    if (!password_verify($code, $row['code_hash'])) {
        echo json_encode(['success' => false, 'error' => '❌ Incorrect code. Please try again.']);
        exit();
    }

    // Code is valid — return user_id so Step 3 can submit
    echo json_encode(['success' => true, 'user_id' => $user['id']]);
    exit();
}

// ── STEP 2: Set the new password ──────────────────────────────────────────────
if ($action === 'set_password') {
    $user_id  = (int)($_POST['user_id'] ?? 0);
    $new_pass = $_POST['new_password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if (!$user_id || !$new_pass) {
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

    // Make sure a valid (unexpired) code row still exists for this user_id
    // This prevents anyone from calling set_password with a random user_id
    $expCheck = $pdo->prepare("SELECT (expires_at < NOW()) AS expired FROM reset_codes WHERE user_id = :id");
    $expCheck->execute([':id' => $user_id]);
    $expRow = $expCheck->fetch();

    if (!$expRow) {
        echo json_encode(['success' => false, 'error' => '❌ Session not found. Please start over.']);
        exit();
    }
    if ($expRow['expired']) {
        echo json_encode(['success' => false, 'error' => '❌ Session expired. Please start over.']);
        exit();
    }

    // Update password
    $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
    $pdo->prepare("UPDATE users SET password = :p WHERE id = :id")
        ->execute([':p' => $hashed, ':id' => $user_id]);

    // Clean up
    $pdo->prepare("DELETE FROM reset_codes WHERE user_id = :id")->execute([':id' => $user_id]);

    echo json_encode(['success' => true]);
    exit();
}

echo json_encode(['success' => false, 'error' => 'Invalid action.']);

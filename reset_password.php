<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
include "config/db.php";
header('Content-Type: application/json');

// Enable PDO exceptions so errors don't silently fail
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$action = $_POST['action'] ?? '';

// ── STEP 1: Verify the 6-digit code ──────────────────────────────────────────
if ($action === 'verify_code') {
    $input = trim($_POST['username'] ?? '');
    $code  = trim($_POST['code'] ?? '');

    if (!$input || !$code) {
        echo json_encode(['success' => false, 'error' => 'Username/email and code are required.']);
        exit();
    }

    try {
        // Find user — try username first, then email if column exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE LOWER(username) = LOWER(:u)");
        $stmt->execute([':u' => $input]);
        $user = $stmt->fetch();

        // Also try email column if not found by username
        if (!$user) {
            try {
                $stmt2 = $pdo->prepare("SELECT id FROM users WHERE LOWER(email) = LOWER(:e)");
                $stmt2->execute([':e' => $input]);
                $user = $stmt2->fetch();
            } catch (Exception $e) {
                // email column may not exist, ignore
            }
        }

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

        // Check expiry using DB time
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

        echo json_encode(['success' => true, 'user_id' => $user['id']]);

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => '❌ Server error: ' . $e->getMessage()]);
    }
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

    try {
        // Check reset_codes row still exists (not expired)
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
        $stmt = $pdo->prepare("UPDATE users SET password = :p WHERE id = :id");
        $stmt->execute([':p' => $hashed, ':id' => $user_id]);
        $affected = $stmt->rowCount();

        if ($affected === 0) {
            echo json_encode(['success' => false, 'error' => '❌ Password update failed — user not found (id=' . $user_id . '). Contact your administrator.']);
            exit();
        }

        // Clean up
        $pdo->prepare("DELETE FROM reset_codes WHERE user_id = :id")->execute([':id' => $user_id]);

        echo json_encode(['success' => true]);

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => '❌ Server error: ' . $e->getMessage()]);
    }
    exit();
}

echo json_encode(['success' => false, 'error' => 'Invalid action.']);

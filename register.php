<?php
session_start();
// Security check: Only allow existing admins to create new accounts (optional)
// if(!isset($_SESSION['admin_username'])) { header("Location: index.php"); exit(); }
?>
<!DOCTYPE html>
<html>
<head>
    <title>The Desktop | Register Admin</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .logo-box { background: #c0392b !important; }
        .reg-btn { background: #1fb6ff; border: none; color: white; padding: 12px; width: 100%; border-radius: 8px; cursor: pointer; font-weight: bold; margin-top: 10px;}
    </style>
</head>
<body>
<div class="login-wrapper">
    <div class="login-header">
        <div class="logo-box">📝</div>
        <h1>The<span>Desktop</span></h1>
        <p>System User Registration</p>
    </div>

    <div class="login-card">
        <h2>Register New Admin</h2>
        <form action="register_action.php" method="POST">
            <div class="input-group">
                <label>New Username</label>
                <input type="text" name="reg_user" placeholder="Create username" required>
            </div>
            <div class="input-group" style="margin-top: 15px; margin-bottom: 20px;">
                <label>New Password</label>
                <input type="password" name="reg_pass" placeholder="Create secure password" required>
            </div>
            <button type="submit" class="reg-btn">Create Account</button>
        </form>
        <div style="text-align: center; margin-top: 20px;">
            <a href="admin_login.php" style="color: #8aa0c5; text-decoration: none; font-size: 0.85rem;">← Back to Login</a>
        </div>
    </div>
</div>
</body>
</html>
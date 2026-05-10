<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>The Desktop | Register</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="css/style.css">
<style>
.logo-box{background:#c0392b!important;width:60px;height:60px;border-radius:12px;display:flex;align-items:center;justify-content:center;margin:0 auto 15px;color:white;font-size:28px;box-shadow:0 4px 15px rgba(192,57,43,.4);}
.reg-btn{background:#1fb6ff;border:none;color:white;padding:12px;width:100%;border-radius:8px;cursor:pointer;font-weight:bold;margin-top:10px;font-size:14px;transition:.2s;}
.reg-btn:hover{background:#0ea5e9;}
.error-msg{background:#f8d7da;color:#721c24;padding:10px;border-radius:5px;margin-bottom:15px;text-align:center;font-size:.9rem;border:1px solid #f5c6cb;}
.divider{display:flex;align-items:center;gap:10px;margin:16px 0;color:#64748b;font-size:.82rem;}
.divider::before,.divider::after{content:'';flex:1;height:1px;background:#2d3748;}
.google-btn{display:flex;align-items:center;justify-content:center;gap:10px;width:100%;padding:11px;background:white;color:#333;border:1px solid #ddd;border-radius:8px;cursor:pointer;font-weight:600;font-size:.9rem;transition:.2s;box-sizing:border-box;}
.google-btn:hover{background:#f1f3f4;box-shadow:0 2px 8px rgba(0,0,0,.15);}
.google-btn img{width:20px;height:20px;}
</style>
</head>
<body>
<div class="login-wrapper">
    <div class="login-header">
        <div class="logo-box"><i class="fas fa-user-plus"></i></div>
        <h1>The<span style="color:#e74c3c;">Desktop</span></h1>
        <p>System User Registration</p>
    </div>
    <div class="login-card">
        <h2 style="text-align:center;margin-bottom:16px;">Register New Admin</h2>

        <?php if(isset($_GET['error'])): ?>
            <?php if($_GET['error']==='exists'): ?>
                <div class="error-msg">❌ Username already exists. Try another one.</div>
            <?php elseif($_GET['error']==='google_exists'): ?>
                <div class="error-msg">⚠️ This Google account is already registered. Please login instead.</div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Google Register -->
        <button class="google-btn" onclick="triggerGoogle()">
            <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" alt="G">
            Register with Google
        </button>

        <div class="divider">or register with username</div>

        <form action="register_action.php" method="POST">
            <div class="input-group">
                <label>New Username</label>
                <input type="text" name="reg_user" placeholder="Create username" required>
            </div>
            <div class="input-group" style="margin-top:15px;">
                <label>Email Address</label>
                <input type="email" name="reg_email" placeholder="your@email.com" required>
            </div>
            <div class="input-group" style="margin-top:15px;margin-bottom:5px;">
                <label>New Password</label>
                <input type="password" name="reg_pass" placeholder="Create secure password" required>
            </div>
            <button type="submit" class="reg-btn"><i class="fas fa-user-plus"></i> Create Account</button>
        </form>

        <div style="text-align:center;margin-top:20px;">
            <a href="admin_login.php" style="color:#8aa0c5;text-decoration:none;font-size:.85rem;">← Back to Login</a>
        </div>
    </div>
</div>

<form id="googleForm" action="google_auth.php" method="POST" style="display:none;">
    <input type="hidden" name="credential" id="googleCredential">
    <input type="hidden" name="action" value="register">
</form>

<script src="https://accounts.google.com/gsi/client" async defer></script>
<script>
function triggerGoogle() {
    if (typeof google === 'undefined') { alert('Google Sign-In not loaded.'); return; }
    google.accounts.id.initialize({
        client_id: '<?= getenv("GOOGLE_CLIENT_ID") ?: "" ?>',
        callback: (resp) => {
            document.getElementById('googleCredential').value = resp.credential;
            document.getElementById('googleForm').submit();
        }
    });
    google.accounts.id.prompt();
}
</script>
</body>
</html>

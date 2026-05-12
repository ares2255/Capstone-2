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
.login-card{background:#1a2a3f;border:1px solid #243b5e;border-radius:16px;padding:32px;width:420px;max-width:94vw;margin:30px auto;}
.login-header{text-align:center;margin-bottom:24px;}
.input-group{margin-bottom:0;}
.input-group label{display:block;font-size:12px;color:#8aa0c5;margin-bottom:6px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;}
.input-group input,
.pass-wrap input{
    width:100%;padding:11px 14px;
    background:#0d1b2e;
    border:1px solid #2d4a6e;
    color:white;border-radius:8px;
    font-size:0.9rem;outline:none;
    box-sizing:border-box;transition:.2s;
}
.input-group input:focus,
.pass-wrap input:focus{border-color:#e74c3c;box-shadow:0 0 0 3px rgba(231,76,60,.15);}
input:-webkit-autofill,
input:-webkit-autofill:hover,
input:-webkit-autofill:focus{
    -webkit-box-shadow:0 0 0 1000px #0d1b2e inset!important;
    -webkit-text-fill-color:white!important;
    border:1px solid #2d4a6e!important;
    transition:background-color 5000s ease-in-out 0s;
}
.pass-wrap{position:relative;display:flex;align-items:center;}
.pass-wrap input{padding-right:44px;}
.eye-btn{position:absolute;right:12px;background:none;border:none;color:#64748b;cursor:pointer;font-size:15px;padding:0;display:flex;align-items:center;transition:.2s;}
.eye-btn:hover{color:#e74c3c;}
.reg-btn{background:#1fb6ff;border:none;color:white;padding:12px;width:100%;border-radius:8px;cursor:pointer;font-weight:bold;margin-top:16px;font-size:14px;transition:.2s;}
.reg-btn:hover{background:#0ea5e9;}
.error-msg{background:#f8d7da;color:#721c24;padding:10px;border-radius:5px;margin-bottom:15px;text-align:center;font-size:.9rem;border:1px solid #f5c6cb;}
.divider{display:flex;align-items:center;gap:10px;margin:16px 0;color:#64748b;font-size:.82rem;}
.divider::before,.divider::after{content:'';flex:1;height:1px;background:#2d3748;}
.google-btn{display:flex;align-items:center;justify-content:center;gap:10px;width:100%;padding:11px;background:white;color:#333;border:1px solid #ddd;border-radius:8px;cursor:pointer;font-weight:600;font-size:.9rem;transition:.2s;box-sizing:border-box;}
.google-btn:hover{background:#f1f3f4;box-shadow:0 2px 8px rgba(0,0,0,.15);}
.google-btn img{width:20px;height:20px;}
.form-group{margin-top:14px;}
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
            <div class="input-group form-group">
                <label>Email Address</label>
                <input type="email" name="reg_email" placeholder="your@email.com" required>
            </div>
            <div class="input-group form-group">
                <label>New Password</label>
                <div class="pass-wrap">
                    <input type="password" name="reg_pass" id="regPass" placeholder="Create secure password" required>
                    <button type="button" class="eye-btn" onclick="togglePass('regPass',this)" tabindex="-1">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
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

<script>
function togglePass(id, btn) {
    const inp = document.getElementById(id);
    const icon = btn.querySelector('i');
    if (inp.type === 'password') {
        inp.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
        btn.style.color = '#e74c3c';
    } else {
        inp.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
        btn.style.color = '';
    }
}

const GOOGLE_CLIENT_ID    = '647107465413-18hemskapc88e4gil1a9g009qpli9074.apps.googleusercontent.com';
const GOOGLE_REDIRECT_URI = 'https://capstone-2-production-c904.up.railway.app/google_callback.php';

function triggerGoogle() {
    const params = new URLSearchParams({
        client_id:     GOOGLE_CLIENT_ID,
        redirect_uri:  GOOGLE_REDIRECT_URI,
        response_type: 'code',
        scope:         'openid email profile',
        access_type:   'online',
        state:         'register',
    });
    window.location.href = 'https://accounts.google.com/o/oauth2/v2/auth?' + params.toString();
}
</script>
</body>
</html>

<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Q-Solutions | Register</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="css/style.css">
<style>
/* ── Page background ── */
body,html{height:100%;margin:0;padding:0;font-family:'Inter',sans-serif;
background:linear-gradient(135deg,#0d1117 0%,#1a1a2e 50%,#16213e 100%);
min-height:100vh;overflow-x:hidden;}

/* ── Logo ── */
.logo-box{display:flex;align-items:center;justify-content:center;gap:0;margin:0 auto 14px;width:fit-content;}
.logo-q{background:#1e2a78;border:3px solid white;width:48px;height:48px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:1.6rem;font-weight:900;color:white;}
.logo-solutions{background:#dde2f0;height:48px;padding:0 14px;border-radius:0 9px 9px 0;display:flex;align-items:center;font-size:1rem;font-weight:700;color:#1e2a78;letter-spacing:2px;text-transform:uppercase;}

/* ── White card ── */
.login-card{
    background:#ffffff;border-radius:24px;
    padding:40px 36px;width:420px;max-width:94vw;
    margin:0 auto;box-shadow:0 10px 40px rgba(0,0,0,0.35);
    border-bottom:8px solid #1e2a78;
    animation:fadeIn .8s ease-out;
}
@keyframes fadeIn{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}

.login-header{text-align:center;margin-bottom:24px;}

/* ── Inputs ── */
.input-group{margin-bottom:0;}
.input-group label{display:block;font-size:11px;color:#64748b;margin-bottom:6px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;}
.input-group input,.pass-wrap input{
    width:100%;padding:11px 14px;
    background:#f8fafc;border:1px solid #e2e8f0;
    color:#1e293b;border-radius:8px;
    font-size:.9rem;outline:none;box-sizing:border-box;transition:.2s;
}
.input-group input:focus,.pass-wrap input:focus{border-color:#1e2a78;box-shadow:0 0 0 3px rgba(30,42,120,.12);}
input:-webkit-autofill,input:-webkit-autofill:hover,input:-webkit-autofill:focus{
    -webkit-box-shadow:0 0 0 1000px #f8fafc inset!important;
    -webkit-text-fill-color:#1e293b!important;
    border:1px solid #e2e8f0!important;
    transition:background-color 5000s ease-in-out 0s;
}
.pass-wrap{position:relative;display:flex;align-items:center;}
.pass-wrap input{padding-right:44px;}
.eye-btn{position:absolute;right:12px;background:none;border:none;color:#94a3b8;cursor:pointer;font-size:15px;padding:0;display:flex;align-items:center;transition:.2s;}
.eye-btn:hover{color:#1e2a78;}

/* ── Register button ── */
.reg-btn{background:#1e2a78;border:none;color:white;padding:13px;width:100%;border-radius:10px;cursor:pointer;font-weight:700;margin-top:16px;font-size:14px;transition:.2s;letter-spacing:.3px;}
.reg-btn:hover{background:#2d3eaa;transform:translateY(-1px);box-shadow:0 4px 16px rgba(30,42,120,.3);}

/* ── Messages ── */
.error-msg{background:#fef2f2;color:#991b1b;padding:10px;border-radius:8px;margin-bottom:15px;text-align:center;font-size:.88rem;border:1px solid #fecaca;}

/* ── Divider ── */
.divider{display:flex;align-items:center;gap:10px;margin:16px 0;color:#94a3b8;font-size:.82rem;}
.divider::before,.divider::after{content:'';flex:1;height:1px;background:#e2e8f0;}

/* ── Google button ── */
.google-btn{display:flex;align-items:center;justify-content:center;gap:10px;width:100%;padding:11px;background:white;color:#333;border:1px solid #e2e8f0;border-radius:8px;cursor:pointer;font-weight:600;font-size:.9rem;transition:.2s;box-sizing:border-box;}
.google-btn:hover{background:#f8fafc;box-shadow:0 2px 8px rgba(0,0,0,.1);}
.google-btn img{width:20px;height:20px;}
.form-group{margin-top:14px;}
</style>
</head>
<body style="display:flex;align-items:center;justify-content:center;min-height:100vh;padding:40px 20px;box-sizing:border-box;">
<div style="width:100%;max-width:500px;animation:fadeIn .8s ease-out;">
    <div class="login-header">
        <div class="logo-box">
            <div class="logo-q">Q</div>
            <div class="logo-solutions">SOLUTIONS</div>
        </div>
        <h1 style="color:white;font-size:2rem;font-weight:700;margin:10px 0 4px;">Q-<span style="color:#7b9cff;">Solutions</span></h1>
        <p style="color:#8aa0c5;margin:0;">System User Registration</p>
    </div>
    <div class="login-card">
        <h2 style="text-align:center;margin-bottom:16px;color:#1e293b;">Register New Admin</h2>

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
            <a href="admin_login.php" style="color:#1e2a78;text-decoration:none;font-size:.85rem;font-weight:600;">← Back to Login</a>
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

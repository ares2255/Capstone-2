<?php
session_start();
include "config/db.php";
if (isset($_SESSION['admin_username'])) { header("Location: dashboard.php"); exit(); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>The Desktop | Login</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="css/style.css">
<style>
.login-header h1 span{color:#e74c3c;}
.logo-box{background:#c0392b!important;width:60px;height:60px;border-radius:12px;display:flex;align-items:center;justify-content:center;margin:0 auto 15px;color:white;font-size:28px;box-shadow:0 4px 15px rgba(192,57,43,.4);}
.login-btn{background:#e74c3c;border:none;color:white;padding:12px;width:100%;border-radius:8px;cursor:pointer;font-weight:bold;transition:.3s;font-size:14px;}
.login-btn:hover{background:#c0392b;}
.error-msg{background:#f8d7da;color:#721c24;padding:10px;border-radius:5px;margin-bottom:15px;text-align:center;font-size:.9rem;border:1px solid #f5c6cb;}
.success-msg{background:#d4edda;color:#155724;padding:10px;border-radius:5px;margin-bottom:15px;text-align:center;font-size:.9rem;border:1px solid #c3e6cb;}
.divider{display:flex;align-items:center;gap:10px;margin:16px 0;color:#64748b;font-size:.82rem;}
.divider::before,.divider::after{content:'';flex:1;height:1px;background:#2d3748;}
.google-btn{display:flex;align-items:center;justify-content:center;gap:10px;width:100%;padding:11px;background:white;color:#333;border:1px solid #ddd;border-radius:8px;cursor:pointer;font-weight:600;font-size:.9rem;transition:.2s;margin-bottom:4px;box-sizing:border-box;}
.google-btn:hover{background:#f1f3f4;box-shadow:0 2px 8px rgba(0,0,0,.15);}
.google-btn img{width:20px;height:20px;}
.forgot-row{text-align:right;margin:-8px 0 14px;}
.forgot-row a{color:#8aa0c5;font-size:.82rem;text-decoration:none;cursor:pointer;}
.forgot-row a:hover{color:#1fb6ff;}
/* Modal */
.modal-bg{display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.75);backdrop-filter:blur(6px);z-index:9999;align-items:center;justify-content:center;}
.modal-bg.show{display:flex;}
.modal-card{background:#0f172a;border:1px solid #1e3a5f;border-radius:14px;padding:30px;width:380px;max-width:94vw;}
.modal-card h3{margin:0 0 6px;color:#38bdf8;font-size:17px;display:flex;align-items:center;gap:8px;}
.modal-card p{color:#64748b;font-size:.84rem;margin:0 0 18px;}
.modal-input{width:100%;padding:10px 12px;background:#020810;border:1px solid #1e293b;color:white;border-radius:8px;font-size:.9rem;outline:none;box-sizing:border-box;margin-bottom:12px;}
.modal-input:focus{border-color:#38bdf8;}
.modal-btn{width:100%;padding:11px;background:#e74c3c;color:white;border:none;border-radius:8px;font-weight:700;cursor:pointer;font-size:.9rem;}
.modal-btn:hover{background:#c0392b;}
.modal-cancel{display:block;text-align:center;margin-top:12px;color:#64748b;font-size:.83rem;cursor:pointer;text-decoration:underline;}
.modal-cancel:hover{color:#8aa0c5;}
/* Reset success box */
.reset-box{background:rgba(56,189,248,.07);border:1px solid rgba(56,189,248,.2);border-radius:10px;padding:18px;text-align:center;margin-top:8px;}
.reset-box .tmp-pass{font-family:monospace;font-size:20px;color:#38bdf8;letter-spacing:2px;font-weight:700;margin:10px 0;}
.reset-box p{color:#8aa0c5;font-size:.83rem;margin:0;}
</style>
</head>
<body>
<div class="login-wrapper">
    <div class="login-header">
        <div class="logo-box"><i class="fas fa-desktop"></i></div>
        <h1>The<span>Desktop</span></h1>
        <p>Admin Control Panel</p>
    </div>
    <div class="login-card">

        <?php if(isset($_GET['status'])): ?>
            <?php if($_GET['status']==='registered'): ?>
                <div class="success-msg">✅ Registration Successful! Please Login.</div>
            <?php elseif($_GET['status']==='google_fail'): ?>
                <div class="error-msg">❌ Google account not registered. Please register first.</div>
            <?php elseif($_GET['status']==='reset_sent'): ?>
                <div class="success-msg">🔑 Password reset! Check below for your temporary password.</div>
            <?php endif; ?>
        <?php endif; ?>
        <?php if(isset($_GET['error'])): ?>
            <div class="error-msg">❌ Invalid Credentials. Please try again.</div>
        <?php endif; ?>

        <!-- Google -->
        <button class="google-btn" onclick="triggerGoogle()">
            <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" alt="G">
            Continue with Google
        </button>

        <div class="divider">or continue with username</div>

        <!-- Login form -->
        <form action="authenticate.php" method="POST">
            <div class="input-group">
                <label>Admin Username</label>
                <input type="text" name="admin_user" placeholder="Enter admin username" required>
            </div>
            <div class="input-group" style="margin-top:15px;">
                <label>Password</label>
                <input type="password" name="admin_pass" placeholder="••••••••" required>
            </div>
            <div class="forgot-row">
                <a onclick="document.getElementById('forgotModal').classList.add('show')">Forgot password?</a>
            </div>
            <button type="submit" class="login-btn"><i class="fas fa-sign-in-alt"></i> Login</button>
        </form>

        <div style="text-align:center;margin-top:15px;">
            <span style="color:#64748b;font-size:.85rem;">Need an account?</span>
            <a href="register.php" style="color:#1fb6ff;text-decoration:none;font-size:.85rem;font-weight:bold;"> Register Here</a>
        </div>
    </div>
</div>

<!-- Forgot Password Modal -->
<div class="modal-bg" id="forgotModal">
    <div class="modal-card">
        <h3><i class="fas fa-key"></i> Reset Password</h3>
        <p>Enter your username. We'll generate a temporary password you can change after logging in.</p>
        <div id="forgotForm">
            <form onsubmit="submitForgot(event)">
                <input type="text" id="forgotUser" class="modal-input" placeholder="Your username" required>
                <button type="submit" class="modal-btn"><i class="fas fa-paper-plane"></i> Reset My Password</button>
            </form>
        </div>
        <div id="forgotResult" style="display:none;"></div>
        <span class="modal-cancel" onclick="closeForgot()">Cancel</span>
    </div>
</div>

<!-- Hidden Google form -->
<form id="googleForm" action="google_auth.php" method="POST" style="display:none;">
    <input type="hidden" name="credential" id="googleCredential">
    <input type="hidden" name="action" value="login">
</form>

<script src="https://accounts.google.com/gsi/client" async defer></script>
<script>
function closeForgot() {
    document.getElementById('forgotModal').classList.remove('show');
    document.getElementById('forgotForm').style.display='block';
    document.getElementById('forgotResult').style.display='none';
    document.getElementById('forgotResult').innerHTML='';
}
document.getElementById('forgotModal').addEventListener('click', e => {
    if(e.target.id==='forgotModal') closeForgot();
});

function submitForgot(e) {
    e.preventDefault();
    const user = document.getElementById('forgotUser').value.trim();
    if (!user) return;
    fetch('forgot_password.php', {
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:'username='+encodeURIComponent(user)
    }).then(r=>r.json()).then(d=>{
        document.getElementById('forgotForm').style.display='none';
        const res = document.getElementById('forgotResult');
        res.style.display='block';
        if(d.success){
            res.innerHTML=`<div class="reset-box">
                <div style="color:#2ecc71;font-size:22px;margin-bottom:8px;">✅ Password Reset!</div>
                <p>Your temporary password is:</p>
                <div class="tmp-pass">${d.temp_pass}</div>
                <p>Login with this password and change it in settings.</p>
            </div>`;
        } else {
            res.innerHTML=`<div class="error-msg">${d.error}</div>
            <button class="modal-btn" onclick="document.getElementById('forgotForm').style.display='block';this.parentElement.style.display='none';" style="margin-top:10px;">Try Again</button>`;
        }
    });
}

function triggerGoogle() {
    if (typeof google === 'undefined') { alert('Google Sign-In not loaded. Check your internet connection.'); return; }
    google.accounts.id.initialize({
        client_id: '<?= getenv("GOOGLE_CLIENT_ID") ?: "" ?>',
        callback: (resp) => {
            document.getElementById('googleCredential').value = resp.credential;
            document.getElementById('googleForm').submit();
        }
    });
    google.accounts.id.prompt((n) => {
        if (n.isNotDisplayed() || n.isSkippedMoment()) {
            google.accounts.id.renderButton(document.createElement('div'), {});
        }
    });
}
</script>
</body>
</html>

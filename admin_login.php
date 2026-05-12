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

/* Login card layout */
.login-card{background:#1a2a3f;border:1px solid #243b5e;border-radius:16px;padding:32px;width:420px;max-width:94vw;margin:30px auto;}
.login-header{text-align:center;margin-bottom:24px;}

/* Input group */
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

/* Fix browser autofill white background */
input:-webkit-autofill,
input:-webkit-autofill:hover,
input:-webkit-autofill:focus{
    -webkit-box-shadow:0 0 0 1000px #0d1b2e inset!important;
    -webkit-text-fill-color:white!important;
    border:1px solid #2d4a6e!important;
    transition:background-color 5000s ease-in-out 0s;
}

.login-btn{background:#e74c3c;border:none;color:white;padding:12px;width:100%;border-radius:8px;cursor:pointer;font-weight:bold;transition:.3s;font-size:14px;margin-top:4px;}
.login-btn:hover{background:#c0392b;}

/* Password wrapper */
.pass-wrap{position:relative;display:flex;align-items:center;}
.pass-wrap input{padding-right:44px;}
.eye-btn{position:absolute;right:12px;background:none;border:none;color:#64748b;cursor:pointer;font-size:15px;padding:0;display:flex;align-items:center;transition:.2s;}
.eye-btn:hover{color:#e74c3c;}
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
.modal-card{background:#0f172a;border:1px solid #1e3a5f;border-radius:14px;padding:30px;width:400px;max-width:94vw;}
.modal-card h3{margin:0 0 6px;color:#38bdf8;font-size:17px;display:flex;align-items:center;gap:8px;}
.modal-card p{color:#64748b;font-size:.84rem;margin:0 0 18px;}
.modal-input{width:100%;padding:10px 12px;background:#020810;border:1px solid #1e293b;color:white;border-radius:8px;font-size:.9rem;outline:none;box-sizing:border-box;margin-bottom:12px;}
.modal-input:focus{border-color:#38bdf8;}
.modal-btn{width:100%;padding:11px;background:#e74c3c;color:white;border:none;border-radius:8px;font-weight:700;cursor:pointer;font-size:.9rem;}
.modal-btn:hover{background:#c0392b;}
.modal-cancel{display:block;text-align:center;margin-top:12px;color:#64748b;font-size:.83rem;cursor:pointer;text-decoration:underline;}
.modal-cancel:hover{color:#8aa0c5;}
.sending-state{text-align:center;padding:20px;color:#8aa0c5;}
.sending-state i{font-size:32px;color:#38bdf8;display:block;margin-bottom:12px;animation:spin 1s linear infinite;}
@keyframes spin{from{transform:rotate(0deg)}to{transform:rotate(360deg)}}
.sent-state{text-align:center;padding:10px 0;}
.sent-state i{font-size:40px;color:#2ecc71;display:block;margin-bottom:10px;}
.sent-state h4{color:white;margin:0 0 6px;}
.sent-state p{color:#8aa0c5;font-size:.84rem;margin:0;}
/* Code input boxes */
.code-inputs{display:flex;gap:8px;justify-content:center;margin-bottom:16px;}
.code-digit{width:46px;height:54px;text-align:center;font-size:1.5rem;font-weight:700;background:#020810;border:2px solid #1e293b;color:white;border-radius:8px;outline:none;transition:.2s;}
.code-digit:focus{border-color:#38bdf8;box-shadow:0 0 0 3px rgba(56,189,248,.15);}
/* Step labels */
.step-badge{display:inline-flex;align-items:center;gap:6px;background:#1e293b;color:#38bdf8;font-size:.75rem;font-weight:700;padding:3px 10px;border-radius:20px;margin-bottom:12px;letter-spacing:.05em;text-transform:uppercase;}
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

        <form action="authenticate.php" method="POST">
            <div class="input-group">
                <label>Admin Username</label>
                <input type="text" name="admin_user" placeholder="Enter admin username" required>
            </div>
            <div class="input-group" style="margin-top:15px;">
                <label>Password</label>
                <div class="pass-wrap">
                    <input type="password" name="admin_pass" id="adminPass" placeholder="••••••••" required>
                    <button type="button" class="eye-btn" onclick="togglePass('adminPass', this)" tabindex="-1">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            <div class="forgot-row">
                <a onclick="document.getElementById('forgotModal').classList.add('show')">Forgot password?</a>
            </div>
            <button type="submit" class="login-btn"><i class="fas fa-sign-in-alt"></i> Login</button>
        </form>
        <script>
        function togglePass(id, btn) {
            const inp = document.getElementById(id);
            const icon = btn.querySelector('i');
            if (inp.type === 'password') {
                inp.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
                btn.style.color = '#38bdf8';
            } else {
                inp.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
                btn.style.color = '';
            }
        }
        </script>

        <div style="text-align:center;margin-top:15px;">
            <span style="color:#64748b;font-size:.85rem;">Need an account?</span>
            <a href="register.php" style="color:#1fb6ff;text-decoration:none;font-size:.85rem;font-weight:bold;"> Register Here</a>
        </div>
    </div>
</div>

<!-- Forgot Password Modal -->
<div class="modal-bg" id="forgotModal">
    <div class="modal-card">

        <!-- STEP 1: Enter username/email -->
        <div id="step1">
            <div class="step-badge"><i class="fas fa-user"></i> Step 1 of 3</div>
            <h3><i class="fas fa-key"></i> Reset Password</h3>
            <p>Enter your username or email and we'll send a 6-digit code to your inbox.</p>
            <input type="text" id="forgotUser" class="modal-input" placeholder="Your email or username">
            <button class="modal-btn" onclick="submitForgot()"><i class="fas fa-paper-plane"></i> Send Code</button>
            <span class="modal-cancel" onclick="closeForgot()">Cancel</span>
        </div>

        <!-- Sending spinner -->
        <div id="sendingState" class="sending-state" style="display:none;">
            <i class="fas fa-spinner"></i>
            Sending code...
        </div>

        <!-- STEP 2: Enter the 6-digit code -->
        <div id="step2" style="display:none;">
            <div class="step-badge"><i class="fas fa-shield-alt"></i> Step 2 of 3</div>
            <h3><i class="fas fa-lock-open"></i> Enter Your Code</h3>
            <p id="step2Desc">A 6-digit code was sent to your email. Enter it below &mdash; it expires in 15 minutes.</p>
            <div class="code-inputs">
                <input class="code-digit" maxlength="1" type="text" inputmode="numeric" pattern="[0-9]">
                <input class="code-digit" maxlength="1" type="text" inputmode="numeric" pattern="[0-9]">
                <input class="code-digit" maxlength="1" type="text" inputmode="numeric" pattern="[0-9]">
                <input class="code-digit" maxlength="1" type="text" inputmode="numeric" pattern="[0-9]">
                <input class="code-digit" maxlength="1" type="text" inputmode="numeric" pattern="[0-9]">
                <input class="code-digit" maxlength="1" type="text" inputmode="numeric" pattern="[0-9]">
            </div>
            <button class="modal-btn" onclick="verifyCode()"><i class="fas fa-check"></i> Verify Code</button>
            <span class="modal-cancel" onclick="closeForgot()">Cancel</span>
        </div>

        <!-- STEP 3: Set new password -->
        <div id="step3" style="display:none;">
            <div class="step-badge"><i class="fas fa-lock"></i> Step 3 of 3</div>
            <h3><i class="fas fa-key"></i> Set New Password</h3>
            <p>Create a new password for your account.</p>
            <input type="password" id="newPass" class="modal-input" placeholder="New password (min. 6 characters)">
            <input type="password" id="confirmPass" class="modal-input" placeholder="Confirm new password">
            <button class="modal-btn" onclick="setPassword()"><i class="fas fa-save"></i> Save New Password</button>
            <span class="modal-cancel" onclick="closeForgot()">Cancel</span>
        </div>

        <!-- Success -->
        <div id="successState" class="sent-state" style="display:none;">
            <i class="fas fa-check-circle"></i>
            <h4>Password Updated!</h4>
            <p>Your password has been changed. You can now log in with your new password.</p>
            <button class="modal-btn" onclick="closeForgot()" style="margin-top:16px;">Back to Login</button>
        </div>

        <!-- Error -->
        <div id="errorState" style="display:none;margin-top:8px;">
            <div class="error-msg" id="errorMsg"></div>
            <button class="modal-btn" onclick="goBackStep()" style="background:#64748b;margin-top:6px;">Try Again</button>
        </div>

    </div>
</div>

<!-- Hidden Google form -->
<form id="googleForm" action="google_auth.php" method="POST" style="display:none;">
    <input type="hidden" name="credential" id="googleCredential">
    <input type="hidden" name="action" value="login">
</form>

<script src="https://cdn.jsdelivr.net/npm/@emailjs/browser@4/dist/email.min.js"></script>
<script src="https://accounts.google.com/gsi/client" async defer></script>
<script>
// ─────────────────────────────────────────────
// CONFIGURATION — fill in your keys here
// ─────────────────────────────────────────────
const EMAILJS_PUBLIC_KEY  = 'iF0sQnadyLlD-2URo';
const EMAILJS_PRIVATE_KEY = 'zAcvRcBOAz7GCxWNlZ424';
const EMAILJS_SERVICE_ID  = 'service_kaimwbk';
const EMAILJS_TEMPLATE_ID = 'template_ku2qrmw';
// ─────────────────────────────────────────────

emailjs.init(EMAILJS_PUBLIC_KEY);

// ── State ────────────────────────────────────────────────────────────────
let _resetUserId  = null;
let _resetToken   = null;
let _resetUser    = null;    // username/email entered in step 1
let _errorFromStep = 1;      // which step to go back to on "Try Again"

// ── Helpers ───────────────────────────────────────────────────────────────
function showOnly(id) {
    ['step1','sendingState','step2','step3','successState','errorState']
        .forEach(s => document.getElementById(s).style.display = 'none');
    document.getElementById(id).style.display = (id === 'sendingState') ? 'block' : 'block';
}

function closeForgot() {
    document.getElementById('forgotModal').classList.remove('show');
    setTimeout(resetForgotForm, 300);
}

function resetForgotForm() {
    _resetUserId = null; _resetToken = null; _resetUser = null;
    document.getElementById('forgotUser').value = '';
    document.getElementById('newPass').value = '';
    document.getElementById('confirmPass').value = '';
    document.querySelectorAll('.code-digit').forEach(i => i.value = '');
    showOnly('step1');
}

function goBackStep() {
    if (_errorFromStep === 2) { showOnly('step2'); }
    else if (_errorFromStep === 3) { showOnly('step3'); }
    else { resetForgotForm(); }
}

function showError(msg, fromStep = 1) {
    _errorFromStep = fromStep;
    document.getElementById('errorMsg').textContent = msg;
    showOnly('errorState');
}

document.getElementById('forgotModal').addEventListener('click', e => {
    if (e.target.id === 'forgotModal') closeForgot();
});

// ── Code input auto-advance ───────────────────────────────────────────────
document.querySelectorAll('.code-digit').forEach((input, idx, all) => {
    input.addEventListener('input', () => {
        input.value = input.value.replace(/\D/g, '').slice(0,1);
        if (input.value && idx < all.length - 1) all[idx + 1].focus();
    });
    input.addEventListener('keydown', e => {
        if (e.key === 'Backspace' && !input.value && idx > 0) all[idx - 1].focus();
        if (e.key === 'Enter') verifyCode();
    });
    input.addEventListener('paste', e => {
        e.preventDefault();
        const digits = (e.clipboardData.getData('text') || '').replace(/\D/g, '').slice(0,6);
        digits.split('').forEach((d,i) => { if (all[i]) all[i].value = d; });
        const next = all[Math.min(digits.length, all.length-1)];
        if (next) next.focus();
    });
});

// ── STEP 1: Request code ───────────────────────────────────────────────────
async function submitForgot() {
    const username = document.getElementById('forgotUser').value.trim();
    if (!username) { alert('Please enter your username or email.'); return; }
    _resetUser = username;

    showOnly('sendingState');

    try {
        const res = await fetch('forgot_password.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'username=' + encodeURIComponent(username)
        });
        const d = await res.json();

        if (!d.success) { showError(d.error, 1); return; }

        // Send 6-digit code via EmailJS
        const emailRes = await fetch('https://api.emailjs.com/api/v1.0/email/send', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'origin': 'https://capstone-2-production-c904.up.railway.app'
            },
            body: JSON.stringify({
                service_id:   EMAILJS_SERVICE_ID,
                template_id:  EMAILJS_TEMPLATE_ID,
                user_id:      EMAILJS_PUBLIC_KEY,
                accessToken:  EMAILJS_PRIVATE_KEY,
                template_params: {
                    email:    d.email,
                    to_name:  d.username,
                    passcode: d.code,
                    time:     '15 minutes',
                }
            })
        });

        if (!emailRes.ok) {
            const errText = await emailRes.text();
            throw new Error('EmailJS: ' + errText);
        }

        document.getElementById('step2Desc').textContent =
            'A 6-digit code was sent to ' + d.email + '. Enter it below — it expires in 15 minutes.';
        showOnly('step2');
        document.querySelectorAll('.code-digit')[0].focus();

    } catch (err) {
        const msg = err?.message || String(err);
        showError('❌ ' + msg, 1);
    }
}

// ── STEP 2: Verify code ────────────────────────────────────────────────────
async function verifyCode() {
    const code = Array.from(document.querySelectorAll('.code-digit')).map(i => i.value).join('');
    if (code.length < 6) { alert('Please enter all 6 digits.'); return; }

    showOnly('sendingState');

    try {
        const res = await fetch('reset_password.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'action=verify_code&username=' + encodeURIComponent(_resetUser) + '&code=' + encodeURIComponent(code)
        });
        const d = await res.json();

        if (!d.success) { showError(d.error, 2); return; }

        _resetUserId = d.user_id;
        showOnly('step3');
        document.getElementById('newPass').focus();

    } catch (err) {
        showError('❌ ' + (err?.message || String(err)), 2);
    }
}

// ── STEP 3: Set new password ───────────────────────────────────────────────
async function setPassword() {
    const np = document.getElementById('newPass').value;
    const cp = document.getElementById('confirmPass').value;

    if (!np || !cp) { alert('Please fill in both password fields.'); return; }
    if (np !== cp)  { alert('Passwords do not match.'); return; }
    if (np.length < 6) { alert('Password must be at least 6 characters.'); return; }

    showOnly('sendingState');

    try {
        const res = await fetch('reset_password.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'action=set_password'
                + '&user_id='          + encodeURIComponent(_resetUserId)
                + '&new_password='     + encodeURIComponent(np)
                + '&confirm_password=' + encodeURIComponent(cp)
        });
        const d = await res.json();

        if (!d.success) { showError(d.error, 3); return; }

        showOnly('successState');

    } catch (err) {
        showError('❌ ' + (err?.message || String(err)), 3);
    }
}

function togglePass(id, btn) {
    const inp = document.getElementById(id);
    const icon = btn.querySelector('i');
    if (inp.type === 'password') {
        inp.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        inp.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}
 — direct OAuth redirect (most reliable)
const GOOGLE_CLIENT_ID    = '647107465413-18hemskapc88e4gil1a9g009qpli9074.apps.googleusercontent.com';
const GOOGLE_REDIRECT_URI = 'https://capstone-2-production-c904.up.railway.app/google_callback.php';

function triggerGoogle() {
    const params = new URLSearchParams({
        client_id:     GOOGLE_CLIENT_ID,
        redirect_uri:  GOOGLE_REDIRECT_URI,
        response_type: 'code',
        scope:         'openid email profile',
        access_type:   'online',
        state:         'login',
    });
    window.location.href = 'https://accounts.google.com/o/oauth2/v2/auth?' + params.toString();
}
</script>
</body>
</html>

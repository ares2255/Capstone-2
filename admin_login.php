    <?php
    session_start();
    include "config/db.php";

    // Redirect to dashboard if already logged in as admin
    if(isset($_SESSION['admin_username'])){
        header("Location: dashboard.php");
        exit();
    }
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>The Desktop </title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <link rel="stylesheet" href="css/style.css">
        <style>
            /* Themed Adjustments */
            .login-header h1 span { color: #e74c3c; } 
            
            .logo-box { 
                background: #c0392b !important; 
                width: 60px;
                height: 60px;
                border-radius: 12px; /* Sleek rounded square */
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto 15px;
                color: white;
                font-size: 28px;
                box-shadow: 0 4px 15px rgba(192, 57, 43, 0.4);
            }

            .login-btn { 
                background: #e74c3c; 
                border: none; 
                color: white; 
                padding: 12px; 
                width: 100%; 
                border-radius: 8px; 
                cursor: pointer; 
                font-weight: bold;
                transition: 0.3s;
            }
            .login-btn:hover { background: #c0392b; }
            
            .error-msg {
                background: #f8d7da;
                color: #721c24;
                padding: 10px;
                border-radius: 5px;
                margin-bottom: 15px;
                text-align: center;
                font-size: 0.9rem;
                border: 1px solid #f5c6cb;
            }
        </style>
    </head>
    <body>
    <div class="login-wrapper">
        <div class="login-header">
            <div class="logo-box">
                <i class="fas fa-desktop"></i>
            </div>
            <h1>The<span>Desktop</span></h1>
            <p>Admin Control Panel</p>
        </div>

        <div class="login-card">
            <h2></h2>
            
            <?php if(isset($_GET['status']) && $_GET['status'] == 'registered'): ?>
                <div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center; font-size: 0.9rem; border: 1px solid #c3e6cb;">
                    ✅ Registration Successful! Please Login.
                </div>
            <?php endif; ?>

            <?php if(isset($_GET['error'])): ?>
                <div class="error-msg">
                    ❌ Invalid Admin Credentials
                </div>
            <?php endif; ?>

            <form action="authenticate.php" method="POST">
                <div class="input-group">
                    <label>Admin Username</label>
                    <input type="text" name="admin_user" placeholder="Enter admin username" required>
                </div>
                <div class="input-group" style="margin-top: 15px; margin-bottom: 20px;">
                    <label>Password</label>
                    <input type="password" name="admin_pass" placeholder="••••••••" required>
                </div>
                <button type="submit" class="login-btn">Login</button>
            </form>

            <div style="text-align: center; margin-top: 15px;">
                <span style="color: #64748b; font-size: 0.85rem;">Need an account?</span> 
                <a href="register.php" style="color: #1fb6ff; text-decoration: none; font-size: 0.85rem; font-weight: bold;">Register Here</a>
            </div>
        </div>
    </div>
    </body>
    </html>
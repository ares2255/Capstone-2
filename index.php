<?php
session_start();
include_once "config/db.php"; 

// Redirect if already logged in
if(isset($_SESSION['username'])){
    header("Location: counter.php");
    exit();
}
if(isset($_SESSION['admin_username'])){
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Q-Solutions | Welcome</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-bg: #1a1a2e;
            --card-bg: #ffffff;
            --qs-navy: #1e2a78;
            --qs-blue: #2d3eaa;
            --qs-accent: #4a6cf7;
            --text-main: #1e293b;
            --text-muted: #64748b;
        }

        body, html {
            height: 100%;
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0d1117 0%, #1a1a2e 50%, #16213e 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .login-wrapper {
            width: 100%;
            max-width: 900px;
            padding: 40px;
            text-align: center;
            background: transparent;
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-header { margin-bottom: 50px; }

        .logo-box {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0;
            margin: 0 auto 18px;
            width: fit-content;
        }

        .logo-q {
            background: var(--qs-navy);
            border: 3px solid white;
            width: 56px;
            height: 56px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: 900;
            color: white;
            font-family: 'Inter', sans-serif;
            letter-spacing: -1px;
        }

        .logo-solutions {
            background: #dde2f0;
            height: 56px;
            padding: 0 18px;
            border-radius: 0 10px 10px 0;
            display: flex;
            align-items: center;
            font-size: 1.35rem;
            font-weight: 700;
            color: var(--qs-navy);
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .login-header h1 {
            font-size: 2.2rem;
            color: white;
            margin: 14px 0 0;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .login-header h1 span { color: #7b9cff; }

        .login-header p {
            font-size: 1.1rem;
            color: #8aa0c5;
            margin-top: 10px;
        }

        .portal-container {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        .portal-card {
            background: var(--card-bg);
            padding: 60px 40px;
            border-radius: 24px;
            text-decoration: none;
            width: 100%;
            max-width: 350px;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: flex;
            flex-direction: column;
            align-items: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.4);
            border-bottom: 8px solid var(--qs-navy);
        }

        .portal-card:hover {
            transform: translateY(-12px);
            box-shadow: 0 25px 60px rgba(74,108,247,0.3);
        }

        .portal-icon {
            font-size: 5rem;
            margin-bottom: 30px;
            color: var(--qs-blue);
        }

        .portal-card h3 {
            font-size: 2rem;
            margin: 0 0 15px 0;
            color: var(--text-main);
            font-weight: 700;
        }

        .portal-card p {
            font-size: 1.1rem;
            color: var(--text-muted);
            line-height: 1.6;
            margin: 0;
        }

        .copyright {
            margin-top: 80px;
            color: #475569;
            font-size: 0.85rem;
            font-weight: 600;
            letter-spacing: 1.5px;
            opacity: 0.6;
        }
    </style>
</head>
<body>

<div class="login-wrapper">
    <div class="login-header">
        <div class="logo-box">
            <img src="logo.jpg" alt="Q Solutions" style="height:64px;width:auto;object-fit:contain;display:block;margin:0 auto 14px;">
        </div>
        <h1>Q-<span>Solutions</span></h1>
        <p>Management &amp; Analytics Portal</p>
    </div>

    <div class="portal-container">
        <a href="admin_login.php" class="portal-card">
            <div class="portal-icon">
                <i class="fas fa-user-shield"></i>
            </div>
            <h3>Admin Portal</h3>
            <p>View analytics, adjust rental rates, and manage system users.</p>
        </a>
    </div>

    <p class="copyright">
        &copy; <?php echo date("Y"); ?> Q-SOLUTIONS &bull; ADMIN ACCESS ONLY
    </p>
</div>

</body>
</html>

<?php
session_start();
include "config/db.php";

if(!isset($_SESSION['admin_username']) && !isset($_SESSION['username'])){
    header("Location: index.php");
    exit();
}

$is_admin = isset($_SESSION['admin_username']);
$display_user = $is_admin ? $_SESSION['admin_username'] : $_SESSION['username'];

// --- FETCH DYNAMIC RATES ---
$rateQuery = mysqli_query($conn, "SELECT bw_rate, color_rate FROM settings LIMIT 1");
$rates = mysqli_fetch_assoc($rateQuery);
$db_bw_rate = $rates['bw_rate'] ?? 5.00;
$db_color_rate = $rates['color_rate'] ?? 15.00;

// --- FIXED PRINTING STATS ---
$today = date('Y-m-d');
$statsQuery = mysqli_query($conn, "SELECT SUM(price) as rev, SUM(pages) as pgs FROM print_jobs WHERE DATE(created_at) = '$today'");
$stats = mysqli_fetch_assoc($statsQuery);

$today_rev = floatval($stats['rev'] ?? 0);
$today_pages = intval($stats['pgs'] ?? 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Desktop | Printing</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* LOCK: Stops the nav bar from shifting */
        html { overflow-y: scroll; }

        body { 
            background-color: #050b14; 
            background-image: 
                linear-gradient(rgba(19, 39, 66, 0.3) 1px, transparent 1px),
                linear-gradient(90deg, rgba(19, 39, 66, 0.3) 1px, transparent 1px);
            background-size: 50px 50px; 
            color: white; 
            font-family: 'Segoe UI', sans-serif; 
            margin: 0; 
            min-height: 100vh;
        }

        .header {
            display: flex; justify-content: space-between; align-items: center;
            padding: 12px 30px; background-color: #0a0e14;
            border-bottom: 1px solid #1e293b; position: relative;
            height: 70px; box-sizing: border-box;
        }

        .header::after {
            content: ''; position: absolute; bottom: 0; left: 0; right: 0;
            height: 1px; background: linear-gradient(90deg, transparent, #ff4d4d, transparent);
        }

        .logo-container { display: flex; align-items: center; gap: 10px; }
        .logo-icon {
            color: #ff4d4d; font-size: 18px; border: 2px solid #ff4d4d;
            border-radius: 50%; width: 28px; height: 28px;
            display: flex; align-items: center; justify-content: center;
        }
        .logo-text { font-weight: bold; font-size: 20px; color: #38bdf8; }
        .logo-text span { color: #ff4d4d; }

        .nav-links { display: flex; gap: 35px; }
        .nav-item {
            text-decoration: none; color: #94a3b8;
            display: flex; align-items: center; gap: 8px;
            font-size: 14px; padding: 8px 0; transition: 0.3s;
            border-bottom: 2px solid transparent;
        }
        .nav-item.active { color: #ff4d4d; border-bottom: 2px solid #ff4d4d; }
        .nav-item:hover { color: #ffffff; }

        .header-right { display: flex; align-items: center; gap: 15px; }
        #systemClock {
            color: #38bdf8; font-family: monospace;
            background: rgba(56, 189, 248, 0.1);
            padding: 5px 12px; border-radius: 4px;
            font-weight: bold; font-size: 14px;
        }

        .admin-badge {
            background: #1e293b; color: #94a3b8;
            padding: 6px 12px; border-radius: 5px;
            font-size: 13px; display: flex; align-items: center; gap: 6px;
        }

        .logout-btn {
            background: #ff4d4d; color: white; border: none;
            padding: 7px 16px; border-radius: 6px;
            cursor: pointer; text-decoration: none;
            font-weight: bold; font-size: 13px;
        }

        .main-container { max-width: 1400px; margin: 0 auto; padding: 40px; display: flex; gap: 25px; }
        .left-col { flex: 2; }
        .right-col { flex: 1; }

        .panel-card { background: rgba(10, 25, 47, 0.8); border: 1px solid #132742; border-radius: 12px; padding: 25px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { text-align: left; color: #8aa0c5; font-size: 12px; border-bottom: 1px solid #132742; padding: 12px; text-transform: uppercase; }
        td { padding: 15px 12px; font-size: 14px; border-bottom: 1px solid #132742; }

        .type-badge { background: #132742; padding: 4px 10px; border-radius: 4px; font-size: 11px; }
        .price-text { color: #2ecc71; font-weight: bold; }
        .void-btn { color: #ff4d4d; background: none; border: none; cursor: pointer; transition: 0.2s; }

        /* Stats styling */
        .stat-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px; }
        .stat-box { background: rgba(10, 25, 47, 0.8); border: 1px solid #132742; padding: 20px; border-radius: 12px; text-align: center; }
        .stat-val { display: block; font-size: 20px; font-weight: bold; color: #2ecc71; }
        .stat-lbl { font-size: 10px; color: #8aa0c5; text-transform: uppercase; letter-spacing: 1px; }

        label { display: block; font-size: 12px; color: #8aa0c5; margin-bottom: 8px; margin-top: 15px; }
        input { width: 100%; padding: 12px; background: #050b14; border: 1px solid #132742; color: white; border-radius: 8px; box-sizing: border-box; }
        
        .toggle-row { display: flex; background: #050b14; border-radius: 8px; padding: 5px; border: 1px solid #132742; }
        .toggle-btn { flex: 1; border: none; background: none; color: #8aa0c5; padding: 10px; cursor: pointer; border-radius: 6px; font-weight: bold; transition: 0.3s; }
        .toggle-btn.active { background: #38bdf8; color: white; }

        .confirm-btn { width: 100%; background: #2ecc71; color: white; border: none; padding: 15px; border-radius: 10px; font-weight: bold; cursor: pointer; margin-top: 20px; transition: 0.3s; }
        
        .toast-success {
            position: fixed; top: 20px; right: 20px; background: #2ecc71; color: white;
            padding: 15px 25px; border-radius: 8px; z-index: 1000; animation: slideIn 0.5s ease-out;
        }
        @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }

        #voidModal {
            display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.85); z-index: 9999; align-items: center; justify-content: center;
        }
    </style>
</head>
<body>

    <header class="header">
        <div class="logo-container">
            <div class="logo-icon"><i class="fas fa-cog"></i></div>
            <div class="logo-text">The<span>Desktop</span></div>
        </div>
        <nav class="nav-links">
            <a href="counter.php" class="nav-item"><i class="fas fa-list-ul"></i> Counter</a>
            <a href="printing.php" class="nav-item active"><i class="fas fa-print"></i> Printing</a>
            <a href="dashboard.php" class="nav-item"><i class="fas fa-chart-pie"></i> Dashboard</a>
            <a href="settings.php" class="nav-item"><i class="fas fa-cog"></i> Settings</a>
        </nav>
        <div class="header-right">
            <div id="systemClock">00:00:00 AM</div>
            <div class="admin-badge"><i class="fas fa-user"></i> <?php echo htmlspecialchars($display_user); ?></div>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </header>

    <div class="main-container">
        <div class="left-col">
            <h2 style="margin: 0;">Printing Management</h2>
            <p style="color: #8aa0c5; font-size: 14px; margin-bottom: 30px;">Record and track customer print jobs</p>

            <div class="panel-card">
                <h3 style="color: #38bdf8; margin: 0 0 20px 0; font-size: 16px;">Recent Print Logs</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Type</th><th>Pages</th><th>Price</th><th>Time</th><th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $printLogs = mysqli_query($conn, "SELECT * FROM print_jobs ORDER BY created_at DESC LIMIT 10");
                        while($log = mysqli_fetch_assoc($printLogs)):
                        ?>
                        <tr>
                            <td><span class="type-badge"><?php echo $log['type']; ?></span></td>
                            <td><?php echo $log['pages']; ?> Pages</td>
                            <td class="price-text">₱<?php echo number_format($log['price'], 2); ?></td>
                            <td style="color: #8aa0c5;"><?php echo date('g:i A', strtotime($log['created_at'])); ?></td>
                            <td><button class="void-btn" onclick="voidPrint(<?php echo $log['id']; ?>)"><i class="fas fa-trash-alt"></i></button></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

    
            <div class="panel-card">
                <h3 style="color: #38bdf8; margin: 0; font-size: 16px;"><i class="fas fa-plus-circle"></i> New Print Job</h3>
                <form action="save_print.php" method="POST">
                    <label>Paper Type</label>
                    <div class="toggle-row">
                        <button type="button" class="toggle-btn active" onclick="setType('BW', this)">B&W</button>
                        <button type="button" class="toggle-btn" onclick="setType('Color', this)">Color</button>
                    </div>
                    <input type="hidden" name="print_type" id="print_type" value="BW">
                    
                    <label>Number of Pages</label>
                    <input type="number" name="pages" id="pages" value="1" min="1" oninput="calculateTotal()">
                    
                    <div style="background:#050b14; padding:15px; border-radius:10px; margin-top:20px; display:flex; justify-content:space-between; border: 1px solid #132742;">
                        <span style="font-size:13px; color: #8aa0c5;">Calculated Price:</span>
                        <span class="price-text" id="display_total">₱0.00</span>
                    </div>
                    <button type="submit" class="confirm-btn">Confirm & Save</button>
                </form>
            </div>
        </div>
    </div>

    <div id="voidModal">
        <div class="panel-card" style="width:400px; text-align:center; border: 1px solid #ff4d4d; box-shadow: 0 0 20px rgba(255, 77, 77, 0.2);">
            <i class="fas fa-exclamation-triangle" style="color: #ff4d4d; font-size: 40px; margin-bottom: 15px;"></i>
            <h3 style="color: white; margin-bottom: 10px;">Void Print Job?</h3>
            <p style="color: #8aa0c5; font-size: 14px; margin-bottom: 25px;">This action will remove the record and deduct it from your daily revenue.</p>
            <div style="display:flex; gap:10px;">
                <button onclick="closeVoidModal()" style="flex:1; background:#1e293b; color:white; border:none; padding:12px; border-radius:8px; cursor:pointer; font-weight:bold;">Cancel</button>
                <button id="confirmVoidBtn" style="flex:1; background:#ff4d4d; color:white; border:none; padding:12px; border-radius:8px; cursor:pointer; font-weight:bold;">Yes, Void it</button>
            </div>
        </div>
    </div>

    <script>
        let currentType = 'BW';
        const bwRate = <?php echo $db_bw_rate; ?>;
        const colorRate = <?php echo $db_color_rate; ?>;

        function setType(type, btn) {
            currentType = type;
            document.getElementById('print_type').value = type;
            document.querySelectorAll('.toggle-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            calculateTotal();
        }

        function calculateTotal() {
            const pages = document.getElementById('pages').value || 0;
            const rate = currentType === 'BW' ? bwRate : colorRate;
            const total = pages * rate;
            document.getElementById('display_total').innerText = "₱" + total.toFixed(2);
        }

        window.onload = calculateTotal;

        let targetVoidId = null;
        function voidPrint(id) {
            targetVoidId = id;
            document.getElementById('voidModal').style.display = 'flex';
            document.getElementById('confirmVoidBtn').onclick = function() {
                window.location.href = "void_print.php?id=" + targetVoidId;
            };
        }
        function closeVoidModal() {
            document.getElementById('voidModal').style.display = 'none';
        }

        function updateClock() {
            const now = new Date();
            document.getElementById('systemClock').innerText = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
        }
        setInterval(updateClock, 1000);
        updateClock();

        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('msg') === 'removed') {
            const toast = document.createElement('div');
            toast.className = 'toast-success';
            toast.innerHTML = '<i class="fas fa-check-circle"></i> Print job removed successfully!';
            document.body.appendChild(toast);
            setTimeout(() => { toast.remove(); }, 3000);
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    </script>
</body>
</html>
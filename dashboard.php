<?php
session_start();
include "config/db.php";

if(!isset($_SESSION['admin_username']) && !isset($_SESSION['username'])){
    header("Location: index.php");
    exit();
}

$is_admin = isset($_SESSION['admin_username']);
$display_user = $is_admin ? $_SESSION['admin_username'] : $_SESSION['username'];

// --- FETCH DATA ---
$activeQuery = mysqli_query($conn,"SELECT COUNT(*) as total FROM pcs WHERE status='active'");
$active = mysqli_fetch_assoc($activeQuery)['total'];

$pcRevQuery = mysqli_query($conn,"SELECT SUM(cost) as revenue FROM sessions WHERE DATE(start_time)=CURDATE()");
$pc_revenue = mysqli_fetch_assoc($pcRevQuery)['revenue'] ?? 0;

$printRevQuery = mysqli_query($conn,"SELECT SUM(price) as revenue FROM print_jobs WHERE DATE(created_at)=CURDATE()");
$print_revenue = mysqli_fetch_assoc($printRevQuery)['revenue'] ?? 0;

$total_combined_revenue = $pc_revenue + $print_revenue;

$sessQuery = mysqli_query($conn,"SELECT COUNT(*) as total FROM sessions WHERE DATE(start_time)=CURDATE()");
$sessions = mysqli_fetch_assoc($sessQuery)['total'];

$printQuery = mysqli_query($conn,"SELECT COUNT(*) as total FROM print_jobs WHERE DATE(created_at)=CURDATE()");
$prints = mysqli_fetch_assoc($printQuery)['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Desktop | Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        html { overflow-y: scroll; scrollbar-gutter: stable; }
        body { 
            background-color: #050b14; 
            background-image: linear-gradient(rgba(19, 39, 66, 0.3) 1px, transparent 1px), linear-gradient(90deg, rgba(19, 39, 66, 0.3) 1px, transparent 1px); 
            background-size: 50px 50px; 
            color: white; 
            font-family: 'Segoe UI', sans-serif; 
            margin: 0; 
            min-height: 100vh; 
        }

        /* HEADER */
        .header { 
            display: flex; justify-content: space-between; align-items: center; 
            padding: 0 30px; background-color: #0a0e14; 
            border-bottom: 1px solid #1e293b; position: relative; 
            height: 70px; box-sizing: border-box; 
        }
        .header::after { content: ''; position: absolute; bottom: 0; left: 0; right: 0; height: 1px; background: linear-gradient(90deg, transparent, #ff4d4d, transparent); }
        
        .logo-container { display: flex; align-items: center; gap: 10px; }
        .logo-icon { color: #ff4d4d; font-size: 18px; border: 2px solid #ff4d4d; border-radius: 50%; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; }
        .logo-text { font-weight: bold; font-size: 20px; color: #38bdf8; }
        .logo-text span { color: #ff4d4d; }

        .nav-links { display: flex; gap: 35px; }
        .nav-item { text-decoration: none; color: #94a3b8; display: flex; align-items: center; gap: 8px; font-size: 14px; transition: 0.3s; border-bottom: 2px solid transparent; padding: 8px 0; }
        .nav-item.active { color: #ff4d4d; border-bottom: 2px solid #ff4d4d; }
        
        .header-right { display: flex; align-items: center; gap: 15px; }
        #systemClock { color: #38bdf8; font-family: monospace; background: rgba(56, 189, 248, 0.1); padding: 5px 12px; border-radius: 4px; font-weight: bold; font-size: 14px; }
        .admin-badge { background: #1e293b; color: #94a3b8; padding: 6px 12px; border-radius: 5px; font-size: 13px; display: flex; align-items: center; gap: 6px; }
        .logout-btn { background: #ff4d4d; color: white; border: none; padding: 7px 16px; border-radius: 6px; cursor: pointer; text-decoration: none; font-weight: bold; font-size: 13px; }

        /* WIDE CONTAINER WIDTH */
        .main-container { max-width: 1400px; margin: 0 auto; padding: 40px; }
        
        .stat-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 40px; }
        .stat-card { background: rgba(10, 25, 47, 0.8); border: 1px solid #132742; padding: 25px; border-radius: 12px; }

        /* EXPANSIVE AND BALANCED TABLE FIX */
        .table-container { background: rgba(10, 25, 47, 0.8); border: 1px solid #132742; border-radius: 12px; padding: 30px; }
        table { width: 100%; border-collapse: collapse; font-size: 14px; table-layout: fixed; }
        th { text-align: left; color: #8aa0c5; border-bottom: 1px solid #132742; padding: 15px 0; font-weight: 600; text-transform: uppercase; font-size: 11px; letter-spacing: 1.5px; }
        td { padding: 20px 0; border-bottom: 1px solid #132742; color: #cbd5e1; vertical-align: middle; }
        
        /* Using percentages to enforce spacing like image_fc029f.png */
        .col-type   { width: right; } /* Type label compact */
        .col-desc   { width: auto; } /* Acts as flexible pusher space */
        .col-amt    { width: 22%; text-align: left; }
        .col-time   { width: 20%; text-align: left; }
        .col-action { width: 10%; text-align: left; } 

        .trash-btn { color: #ff4d4d; cursor: pointer; opacity: 0.7; transition: 0.2s; font-size: 18px; }
        .trash-btn:hover { opacity: 1; transform: scale(1.1); }

        /* MODAL */
        .custom-modal { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.85); backdrop-filter: blur(5px); display: flex; align-items: center; justify-content: center; z-index: 2000; }
        .modal-content { background: #0f172a; border: 1px solid #1e293b; padding: 30px; border-radius: 15px; text-align: center; max-width: 400px; width: 90%; }
        .modal-icon { color: #ff4d4d; font-size: 50px; margin-bottom: 20px; }
        .btn-cancel { background: #1e293b; color: #94a3b8; border: none; padding: 12px 25px; border-radius: 8px; cursor: pointer; flex: 1; }
        .btn-confirm { background: #ff4d4d; color: white; border: none; padding: 12px 25px; border-radius: 8px; cursor: pointer; font-weight: bold; flex: 1; }
    </style>
</head>
<body>

    <header class="header">
        <div class="logo-container">
            <div class="logo-icon"><i class="fas fa-chart-pie"></i></div>
            <div class="logo-text">The<span>Desktop</span></div>
        </div>
        <nav class="nav-links">
            <a href="counter.php" class="nav-item"><i class="fas fa-list-ul"></i> Counter</a>
            <a href="printing.php" class="nav-item"><i class="fas fa-print"></i> Printing</a>
            <a href="dashboard.php" class="nav-item active"><i class="fas fa-chart-pie"></i> Dashboard</a>
            <a href="settings.php" class="nav-item"><i class="fas fa-cog"></i> Settings</a>
        </nav>
        <div class="header-right">
            <div id="systemClock">00:00:00 AM</div>
            <div class="admin-badge"><i class="fas fa-user"></i> <?php echo htmlspecialchars($display_user); ?></div>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </header>

    <div class="main-container">
        <div style="margin-bottom: 30px;">
            <h2 style="margin: 0; font-size: 24px;"><?php echo $is_admin ? 'System Overview' : 'Staff Dashboard'; ?></h2>
            <p style="color: #8aa0c5; font-size: 14px;">Real-time revenue and transaction logs</p>
        </div>

        <div class="stat-grid">
            <div class="stat-card">
                <h2 style="color: #2ecc71; margin: 0;">₱<?php echo number_format($total_combined_revenue, 2); ?></h2>
                <small style="color: #8aa0c5; text-transform: uppercase; font-size: 10px; letter-spacing: 1px;">Today's Revenue</small>
            </div>
            <div class="stat-card">
                <h2 style="margin: 0; color: #f1c40f;"><?php echo $sessions; ?></h2>
                <small style="color: #8aa0c5; text-transform: uppercase; font-size: 10px; letter-spacing: 1px;">Sessions Today</small>
            </div>
            <div class="stat-card">
                <h2 style="margin: 0; color: #a855f7;"><?php echo $prints; ?></h2>
                <small style="color: #8aa0c5; text-transform: uppercase; font-size: 10px; letter-spacing: 1px;">Print Jobs Today</small>
            </div>
            <div class="stat-card">
                <h2 style="color: #1fb6ff; margin: 0;"><?php echo $active; ?></h2>
                <small style="color: #8aa0c5; text-transform: uppercase; font-size: 10px; letter-spacing: 1px;">Active PCs</small>
            </div>
        </div>

        <div class="table-container">
            <h3 style="margin-top: 0; font-size: 16px; margin-bottom: 25px; color: #38bdf8;">Recent Transaction History</h3>
            <table>
                <thead>
                    <tr>
                        <th class="col-type">TYPE</th>
                        <th class="col-desc">DESCRIPTION</th>
                        <th class="col-amt">AMOUNT</th>
                        <th class="col-time">TIME</th>
                        <th class="col-action">ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $history = mysqli_query($conn,"
                        (SELECT 'Session' as type, sessions.id as trans_id, pcs.name as description, sessions.cost as price, sessions.end_time as date
                        FROM sessions JOIN pcs ON pcs.id=sessions.pc_id
                        WHERE DATE(sessions.end_time) = CURDATE() OR sessions.end_time IS NULL)
                        UNION
                        (SELECT 'Print' as type, id as trans_id, CONCAT(type,' print ',pages,' pages') as description, price, created_at as date 
                        FROM print_jobs
                        WHERE DATE(created_at) = CURDATE())
                        ORDER BY date DESC LIMIT 10
                    ");
                    while($row = mysqli_fetch_assoc($history)): ?>
                    <tr>
                        <td class="col-type">
                            <span style="border: 1px solid currentColor; padding: 4px 10px; border-radius: 6px; font-size: 11px; color: <?php echo $row['type'] == 'Session' ? '#38bdf8' : '#a855f7'; ?>; background: transparent;">
                                <?php echo $row['type']; ?>
                            </span>
                        </td>
                        <td class="col-desc" style="color: #cbd5e1;"><?php echo $row['description']; ?></td>
                        <td class="col-amt" style="color: #2ecc71; font-weight: bold; font-family: monospace; font-size: 17px;">
                            ₱<?php echo number_format($row['price'], 2); ?>
                        </td>
                        <td class="col-time" style="color: #8aa0c5; font-size: 13px;">
                            <?php echo $row['date'] ? date('g:i A', strtotime($row['date'])) : '<span style="color:#ff4d4d">Active</span>'; ?>
                        </td>
                        <td class="col-action">
                            <i class="fas fa-trash-alt trash-btn" onclick="confirmVoid('<?php echo $row['trans_id']; ?>', '<?php echo $row['type']; ?>')"></i>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="voidModal" class="custom-modal" style="display:none;">
        <div class="modal-content">
            <div class="modal-icon"><i class="fas fa-exclamation-triangle"></i></div>
            <h2>Void Transaction?</h2>
            <p style="color: #94a3b8; font-size: 14px;">This action will remove the record and deduct it from your daily revenue.</p>
            <div style="display: flex; gap: 15px; margin-top: 25px;">
                <button class="btn-cancel" onclick="closeVoidModal()">Cancel</button>
                <button class="btn-confirm" id="confirmVoidBtn">Yes, Void it</button>
            </div>
        </div>
    </div>

    <script>
        function updateClock() {
            const now = new Date();
            document.getElementById('systemClock').innerText = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
        }
        setInterval(updateClock, 1000);
        updateClock();

        function confirmVoid(id, type) {
            document.getElementById('voidModal').style.display = 'flex';
            document.getElementById('confirmVoidBtn').onclick = function() {
                window.location.href = "process_void.php?id=" + id + "&type=" + type;
            };
        }

        function closeVoidModal() { document.getElementById('voidModal').style.display = 'none'; }
        window.onclick = function(event) { if (event.target == document.getElementById('voidModal')) closeVoidModal(); }
        setTimeout(() => { location.reload(); }, 30000);
    </script>
</body>
</html>
<?php
session_start();
include "config/db.php";

if(!isset($_SESSION['admin_username']) && !isset($_SESSION['username'])){
    header("Location: index.php");
    exit();
}

$is_admin = isset($_SESSION['admin_username']);
$display_user = $is_admin ? $_SESSION['admin_username'] : $_SESSION['username'];

// --- FETCH CURRENT SETTINGS ---
$rateQuery = mysqli_query($conn, "SELECT * FROM settings WHERE id = 1");
$rates = mysqli_fetch_assoc($rateQuery);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Desktop | Settings</title>
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
            cursor: pointer; text-decoration: none; font-weight: bold; font-size: 13px;
        }

        /* MAIN LAYOUT */
        .main-content { padding: 40px; display: flex; flex-direction: column; align-items: center; gap: 20px; }
        .cards-wrapper { display: flex; gap: 20px; justify-content: center; flex-wrap: wrap; width: 100%; max-width: 1200px; }

        .card {
            background: rgba(10, 25, 47, 0.8);
            border: 1px solid #132742;
            border-radius: 12px;
            padding: 25px;
            flex: 1;
            min-width: 400px;
        }
        .card h3 { color: #38bdf8; margin-top: 0; display: flex; align-items: center; gap: 10px; border-bottom: 1px solid #132742; padding-bottom: 10px; }
        
        .input-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 15px; }
        label { display: block; font-size: 11px; color: #64748b; margin-bottom: 5px; }
        input, select {
            width: 100%; padding: 10px; background: #050814;
            border: 1px solid #132742; color: white; border-radius: 6px; box-sizing: border-box;
        }
        .btn-save {
            width: 100%; background: #2ecc71; color: white; border: none;
            padding: 12px; border-radius: 8px; margin-top: 20px;
            cursor: pointer; font-weight: bold; transition: 0.3s;
        }
        .btn-save:hover { background: #27ae60; transform: translateY(-2px); }

        .alert-success {
            background: rgba(46, 204, 113, 0.1); color: #2ecc71; border: 1px solid #2ecc71;
            padding: 12px; border-radius: 8px; text-align: center; width: 100%; max-width: 920px;
        }

        /* NEW MODAL STYLES (Matching Void Print Modal) */
        .modal-overlay {
            display: none; 
            position: fixed; 
            top: 0; left: 0; 
            width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.85); 
            z-index: 9999; 
            align-items: center; 
            justify-content: center;
            backdrop-filter: blur(5px);
        }

        .modal-card {
            width: 400px; 
            text-align: center; 
            border: 1px solid #ff4d4d !important; 
            box-shadow: 0 0 30px rgba(255, 77, 77, 0.2);
            padding: 30px !important;
            background: #0a192f;
            border-radius: 12px;
        }

        .modal-icon { color: #ff4d4d; font-size: 50px; margin-bottom: 20px; }
        .modal-card h3 { color: white; margin: 0 0 10px 0; font-size: 22px; border-bottom: none; }
        .modal-card p { color: #8aa0c5; font-size: 14px; line-height: 1.5; margin-bottom: 30px; }
        .modal-actions { display: flex; gap: 15px; }

        .btn-modal-cancel {
            flex: 1; background: #1e293b; color: white; border: none;
            padding: 12px; border-radius: 8px; cursor: pointer; font-weight: bold;
        }
        .btn-modal-confirm {
            flex: 1; background: #ff4d4d; color: white; border: none;
            padding: 12px; border-radius: 8px; cursor: pointer; font-weight: bold;
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
            <a href="printing.php" class="nav-item"><i class="fas fa-print"></i> Printing</a>
            <a href="dashboard.php" class="nav-item"><i class="fas fa-chart-pie"></i> Dashboard</a>
            <a href="settings.php" class="nav-item active"><i class="fas fa-cog"></i> Settings</a>
        </nav>
        <div class="header-right">
            <div id="systemClock">00:00:00 AM</div>
            <div class="admin-badge"><i class="fas fa-user"></i> <?php echo htmlspecialchars($display_user); ?></div>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </header>

    <div class="main-content">
        <?php if(isset($_GET['status']) && $_GET['status'] == 'success'): ?>
            <div class="alert-success"><i class="fas fa-check-circle"></i> Rates updated successfully!</div>
        <?php endif; ?>

        <div class="cards-wrapper">
            <div class="card">
                <h3><i class="fas fa-tag"></i> Pricing & Rates</h3>
                <form action="save_all_rates.php" method="POST">
                    <div class="input-grid">
                        <div><label>1 HR Rate (₱)</label><input type="number" step="0.01" name="hour_rate" value="<?php echo $rates['hourly_rate']; ?>"></div>
                        <div><label>3 HR Rate (₱)</label><input type="number" step="0.01" name="rate_3hr" value="<?php echo $rates['rate_3hr']; ?>"></div>
                        <div><label>5 HR Rate (₱)</label><input type="number" step="0.01" name="rate_5hr" value="<?php echo $rates['rate_5hr']; ?>"></div>
                        <div><label>7 HR Rate (₱)</label><input type="number" step="0.01" name="rate_7hr" value="<?php echo $rates['rate_7hr']; ?>"></div>
                        <div><label>12 HR Rate (₱)</label><input type="number" step="0.01" name="rate_12hr" value="<?php echo $rates['rate_12hr']; ?>"></div>
                        <div><label>Min Charge (₱)</label><input type="number" step="0.01" name="min_charge" value="<?php echo $rates['minimum_charge']; ?>"></div>
                        <div><label>B&W Print (₱)</label><input type="number" step="0.01" name="bw_rate" value="<?php echo $rates['bw_rate']; ?>"></div>
                        <div><label>Color Print (₱)</label><input type="number" step="0.01" name="color_rate" value="<?php echo $rates['color_rate']; ?>"></div>
                    </div>
                    <button type="submit" class="btn-save">Save All Rates</button>
                </form>
            </div>

            <div class="card">
                <h3><i class="fas fa-desktop"></i> PC Management</h3>
                
                <form action="add_specific_pc.php" method="POST">
                    <label>Add New Unit:</label>
                    <div style="display: flex; gap: 10px; margin-bottom: 20px;">
                        <select name="pc_number" required>
                            <option value="" disabled selected>Select PC Number</option>
                            <?php
                            $existing = [];
                            $res = mysqli_query($conn, "SELECT name FROM pcs");
                            if($res) { while($row = mysqli_fetch_assoc($res)) { $existing[] = $row['name']; } }

                            for ($i = 1; $i <= 50; $i++) {
                                $name = "PC-" . str_pad($i, 2, '0', STR_PAD_LEFT);
                                if (!in_array($name, $existing)) echo "<option value='$name'>$name</option>";
                            }
                            ?>
                        </select>
                        <button type="submit" name="add_pc" style="background:#38bdf8; color:white; border:none; padding:0 20px; border-radius:6px; cursor:pointer;">Add</button>
                    </div>
                </form>

                <form action="add_specific_pc.php" method="POST">
                    <label>Remove Specific Unit:</label>
                    <div style="display: flex; gap: 10px; margin-bottom: 20px;">
                        <select name="pc_id" required>
                            <option value="" disabled selected>Select PC</option>
                            <?php
                            $res = mysqli_query($conn, "SELECT id, name FROM pcs ORDER BY name ASC");
                            if($res) { while($row = mysqli_fetch_assoc($res)) { echo "<option value='".$row['id']."'>".$row['name']."</option>"; } }
                            ?>
                        </select>
                        <button type="submit" name="delete_pc" style="background:#ff4d4d; color:white; border:none; padding:0 20px; border-radius:6px; cursor:pointer;">Delete</button>
                    </div>
                </form>

                <form id="clearAllForm" action="add_specific_pc.php" method="POST">
                    <input type="hidden" name="clear_all" value="1">
                    <button type="button" onclick="showClearModal()" style="width:100%; background:transparent; border:1px solid #ff4d4d; color:#ff4d4d; padding:10px; border-radius:8px; cursor:pointer;">Clear All PC Units</button>
                </form>
            </div>
        </div>
    </div>

    <div id="clearUnitsModal" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h3>Clear All Units?</h3>
            <p>This action will permanently remove all PC units from the system. This cannot be undone.</p>
            <div class="modal-actions">
                <button type="button" onclick="closeClearModal()" class="btn-modal-cancel">Cancel</button>
                <button type="button" onclick="submitClearAll()" class="btn-modal-confirm">Yes, Clear All</button>
            </div>
        </div>
    </div>

    <script>
        function updateClock() {
            const now = new Date();
            document.getElementById('systemClock').innerText = now.toLocaleTimeString('en-US', { 
                hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true 
            });
        }
        setInterval(updateClock, 1000);
        updateClock();

        // Modal Logic
        function showClearModal() {
            document.getElementById('clearUnitsModal').style.display = 'flex';
        }

        function closeClearModal() {
            document.getElementById('clearUnitsModal').style.display = 'none';
        }

        function submitClearAll() {
            document.getElementById('clearAllForm').submit();
        }

        // Close modal if clicking outside the card
        window.onclick = function(event) {
            const modal = document.getElementById('clearUnitsModal');
            if (event.target == modal) {
                closeClearModal();
            }
        }
    </script>
</body>
</html>
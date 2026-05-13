<?php
session_start();
include "config/db.php";
date_default_timezone_set('Asia/Manila');

if (!isset($_SESSION['admin_username']) && !isset($_SESSION['username'])) {
    header("Location: index.php"); exit();
}

$is_admin     = isset($_SESSION['admin_username']);
$display_user = $is_admin ? $_SESSION['admin_username'] : $_SESSION['username'];
$current_page = 'printing';

$rates              = $pdo->query("SELECT bw_rate, color_rate, short_bond_rate, long_bond_rate FROM settings LIMIT 1")->fetch();
$db_bw_rate         = $rates['bw_rate']          ?? 2.00;
$db_short_bond_rate = $rates['short_bond_rate']   ?? 0.00;
$db_long_bond_rate  = $rates['long_bond_rate']    ?? 0.00;
$db_color_rate      = $rates['color_rate']        ?? 10.00;

$today       = date('Y-m-d');
$view_date   = (isset($_GET['view_date']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['view_date']))
               ? $_GET['view_date'] : $today;
$is_today    = ($view_date === $today);

$stQ = $pdo->prepare("SELECT COALESCE(SUM(price),0) as rev, COALESCE(SUM(pages),0) as pgs FROM print_jobs WHERE DATE(created_at)=:d");
$stQ->execute([':d' => $view_date]);
$stats       = $stQ->fetch();
$view_rev    = floatval($stats['rev']);
$view_pages  = intval($stats['pgs']);

// Today stats for the stat boxes
$stQ->execute([':d' => $today]);
$todayStats   = $stQ->fetch();
$today_rev    = floatval($todayStats['rev']);
$today_pages  = intval($todayStats['pgs']);

$logsQ = $pdo->prepare("SELECT * FROM print_jobs WHERE DATE(created_at)=:d ORDER BY created_at DESC");
$logsQ->execute([':d' => $view_date]);
$logs  = $logsQ->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>The Desktop | Printing</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="includes/navbar.css">
<script>(function(){if(localStorage.getItem("settings_theme")==="light"){document.documentElement.classList.add("light-mode");}})()</script>
<style>
html{overflow-y:scroll;}
body{background:linear-gradient(135deg,#0d1117 0%,#1a1a2e 50%,#16213e 100%);color:white;font-family:'Inter',sans-serif;margin:0;min-height:100vh;}
.main-container{max-width:1400px;margin:0 auto;padding:36px 40px;display:flex;gap:28px;}
.left-col{flex:2;}.right-col{flex:1;}
.panel-card{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);border-radius:16px;padding:26px;}
.stat-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:20px;}
.stat-box{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);padding:18px;border-radius:16px;text-align:center;border-bottom:4px solid #1e2a78;}
.stat-val{display:block;font-size:20px;font-weight:bold;color:#2ecc71;}
.stat-lbl{font-size:10px;color:#8aa0c5;text-transform:uppercase;letter-spacing:1px;}
table{width:100%;border-collapse:collapse;margin-top:10px;}
th{text-align:left;color:#8aa0c5;font-size:11px;border-bottom:1px solid rgba(255,255,255,.08);padding:11px 12px;text-transform:uppercase;letter-spacing:1px;}
td{padding:14px 12px;font-size:14px;border-bottom:1px solid rgba(255,255,255,.06);}
.type-badge{background:rgba(74,108,247,.15);border:1px solid rgba(74,108,247,.25);color:#7b9cff;padding:3px 9px;border-radius:6px;font-size:11px;}
.price-text{color:#2ecc71;font-weight:bold;}
.void-btn{color:#ff4d4d;background:none;border:none;cursor:pointer;transition:.2s;font-size:15px;}
.void-btn:hover{transform:scale(1.15);}
label{display:block;font-size:12px;color:#8aa0c5;margin:14px 0 6px;}
input{width:100%;padding:11px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);color:white;border-radius:8px;box-sizing:border-box;font-family:'Inter',sans-serif;}
.toggle-row{display:flex;background:rgba(255,255,255,.03);border-radius:10px;padding:4px;border:1px solid rgba(255,255,255,.1);}
.toggle-btn{flex:1;border:none;background:none;color:#8aa0c5;padding:9px;cursor:pointer;border-radius:8px;font-weight:bold;transition:.25s;font-family:'Inter',sans-serif;}
.toggle-btn.active{background:#1e2a78;color:white;}
.confirm-btn{width:100%;background:#1e2a78;color:white;border:none;padding:14px;border-radius:10px;font-weight:bold;cursor:pointer;margin-top:18px;transition:.2s;font-family:'Inter',sans-serif;}
.confirm-btn:hover{background:#2d3eaa;transform:translateY(-1px);}
.price-preview{background:rgba(255,255,255,.04);padding:14px;border-radius:10px;margin-top:16px;display:flex;justify-content:space-between;border:1px solid rgba(255,255,255,.1);}
#voidModal{display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.85);backdrop-filter:blur(5px);z-index:9999;align-items:center;justify-content:center;}
.void-box{background:#ffffff;border-radius:24px;padding:36px;width:400px;text-align:center;border-bottom:8px solid #ff4d4d;box-shadow:0 20px 60px rgba(0,0,0,.5);color:#1e293b;}
.void-box i{color:#ff4d4d;font-size:44px;margin-bottom:14px;display:block;}
.void-box h3{margin:0 0 8px;color:#1e293b;}.void-box p{color:#64748b;font-size:14px;margin-bottom:24px;}
.void-actions{display:flex;gap:12px;}
.btn-cancel{flex:1;background:#f1f5f9;color:#64748b;border:1px solid #e2e8f0;padding:12px;border-radius:8px;cursor:pointer;font-weight:600;transition:.2s;}
.btn-cancel:hover{background:#e2e8f0;}
.btn-confirm{flex:1;background:#ff4d4d;color:white;border:none;padding:12px;border-radius:8px;cursor:pointer;font-weight:bold;transition:.2s;}
.btn-confirm:hover{background:#e03030;}
</style>
</head>
<body>
<?php include 'includes/navbar.php'; ?>
<div class="main-container">
    <div class="left-col">
        <h2 style="margin:0 0 4px;font-size:22px;">Printing Management</h2>
        <p style="color:#8aa0c5;font-size:13px;margin:0 0 24px;">Record and track customer print jobs</p>
        <div class="panel-card">
            <!-- Date Filter -->
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;flex-wrap:wrap;gap:10px;">
                <h3 style="color:#38bdf8;margin:0;font-size:15px;">
                    <?= $is_today ? 'Today\'s Print Logs' : 'Print Logs — ' . date('M d, Y', strtotime($view_date)) ?>
                    <?php if(!$is_today): ?>
                        <a href="printing.php" style="font-size:11px;color:#8aa0c5;margin-left:10px;text-decoration:none;">← Back to Today</a>
                    <?php endif; ?>
                </h3>
                <form method="GET" style="display:flex;align-items:center;gap:8px;">
                    <label style="font-size:11px;color:#8aa0c5;text-transform:uppercase;letter-spacing:.5px;">View Date</label>
                    <input type="date" name="view_date" value="<?= htmlspecialchars($view_date) ?>" max="<?= $today ?>"
                        style="padding:6px 10px;border-radius:8px;border:1px solid rgba(255,255,255,.15);background:rgba(255,255,255,.05);color:white;font-size:13px;cursor:pointer;"
                        onchange="this.form.submit()">
                </form>
            </div>
            <!-- Summary for selected date -->
            <?php if(!$is_today): ?>
            <div style="display:flex;gap:12px;margin-bottom:16px;padding:10px 14px;background:rgba(56,189,248,.07);border:1px solid rgba(56,189,248,.2);border-radius:10px;">
                <span style="font-size:13px;color:#8aa0c5;">Revenue: <strong style="color:#2ecc71;">₱<?= number_format($view_rev,2) ?></strong></span>
                <span style="color:#4a5f7a;">|</span>
                <span style="font-size:13px;color:#8aa0c5;">Pages: <strong style="color:#38bdf8;"><?= $view_pages ?></strong></span>
                <span style="color:#4a5f7a;">|</span>
                <span style="font-size:13px;color:#8aa0c5;">Jobs: <strong style="color:white;"><?= count($logs) ?></strong></span>
            </div>
            <?php endif; ?>
            <table>
                <thead><tr><th>Type</th><th>Pages</th><th>Price</th><th>Time</th><th>Action</th></tr></thead>
                <tbody>
                <?php if(empty($logs)): ?>
                <tr><td colspan="5" style="text-align:center;color:#8aa0c5;padding:28px 0;">No print jobs on <?= date('M d, Y', strtotime($view_date)) ?></td></tr>
                <?php else: ?>
                <?php foreach($logs as $log): ?>
                <tr>
                    <td><span class="type-badge"><?= htmlspecialchars($log['type']) ?></span></td>
                    <td><?= $log['pages'] ?> Pages</td>
                    <td class="price-text">₱<?= number_format($log['price'],2) ?></td>
                    <td style="color:#8aa0c5;"><?= date('g:i A',strtotime($log['created_at'])) ?></td>
                    <td><button class="void-btn" onclick="voidPrint(<?= $log['id'] ?>)"><i class="fas fa-trash-alt"></i></button></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="right-col">
        <div class="stat-grid">
            <div class="stat-box"><span class="stat-val">₱<?= number_format($today_rev,2) ?></span><span class="stat-lbl">Today's Revenue</span></div>
            <div class="stat-box"><span class="stat-val"><?= $today_pages ?></span><span class="stat-lbl">Pages Printed</span></div>
        </div>
        <div class="panel-card">
            <h3 style="color:#38bdf8;margin:0 0 6px;font-size:15px;"><i class="fas fa-plus-circle"></i> New Print Job</h3>
            <form action="save_print.php" method="POST">
                <label>Paper Type</label>
                <div class="toggle-row">
                    <button type="button" class="toggle-btn active" onclick="setType('BW',this)">B&amp;W</button>
                    <button type="button" class="toggle-btn" onclick="setType('Color',this)">Color</button>
                </div>
                <input type="hidden" name="print_type" id="print_type" value="BW">

                <label>Paper Size</label>
                <div class="toggle-row">
                    <button type="button" class="toggle-btn active" onclick="setSize('Short',this)">Short Bond</button>
                    <button type="button" class="toggle-btn" onclick="setSize('Long',this)">Long Bond</button>
                </div>
                <input type="hidden" name="paper_size" id="paper_size" value="Short">

                <label>Number of Pages</label>
                <input type="number" name="pages" id="pages" value="1" min="1" oninput="calculateTotal()">
                <div class="price-preview">
                    <span style="font-size:13px;color:#8aa0c5;">Calculated Price:</span>
                    <span class="price-text" id="display_total">₱0.00</span>
                </div>
                <button type="submit" class="confirm-btn">Confirm &amp; Save</button>
            </form>
        </div>
    </div>
</div>
<div id="voidModal">
    <div class="void-box">
        <i class="fas fa-exclamation-triangle"></i>
        <h3>Void Print Job?</h3>
        <p>This will remove the record and deduct it from daily revenue.</p>
        <div class="void-actions">
            <button class="btn-cancel" onclick="closeVoidModal()">Cancel</button>
            <button class="btn-confirm" id="confirmVoidBtn">Yes, Void it</button>
        </div>
    </div>
</div>
<script>
let currentType='BW', currentSize='Short';
const bwRate=<?= $db_bw_rate ?>, colorRate=<?= $db_color_rate ?>;
const shortBondRate=<?= $db_short_bond_rate ?>, longBondRate=<?= $db_long_bond_rate ?>;

function setType(type,btn){
    currentType=type;
    document.getElementById('print_type').value=type;
    btn.closest('.toggle-row').querySelectorAll('.toggle-btn').forEach(b=>b.classList.remove('active'));
    btn.classList.add('active');
    calculateTotal();
}
function setSize(size,btn){
    currentSize=size;
    document.getElementById('paper_size').value=size;
    btn.closest('.toggle-row').querySelectorAll('.toggle-btn').forEach(b=>b.classList.remove('active'));
    btn.classList.add('active');
    calculateTotal();
}
function calculateTotal(){
    const pages = parseInt(document.getElementById('pages').value) || 0;
    const printRate  = currentType === 'BW' ? bwRate : colorRate;
    const paperRate  = currentSize === 'Short' ? shortBondRate : longBondRate;
    const total = pages * (printRate + paperRate);
    document.getElementById('display_total').innerText = '₱' + total.toFixed(2);
}
window.onload = calculateTotal;
let targetVoidId=null;
function voidPrint(id){targetVoidId=id;document.getElementById('voidModal').style.display='flex';document.getElementById('confirmVoidBtn').onclick=()=>{window.location.href='void_print.php?id='+targetVoidId;};}
function closeVoidModal(){document.getElementById('voidModal').style.display='none';}
window.onclick=e=>{if(e.target==document.getElementById('voidModal'))closeVoidModal();}
</script>
</body></html>

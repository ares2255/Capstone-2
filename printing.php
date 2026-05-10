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

$rates         = $pdo->query("SELECT bw_rate, color_rate FROM settings LIMIT 1")->fetch();
$db_bw_rate    = $rates['bw_rate']    ?? 5.00;
$db_color_rate = $rates['color_rate'] ?? 15.00;

$today    = date('Y-m-d');
$stQ      = $pdo->prepare("SELECT COALESCE(SUM(price),0) as rev, COALESCE(SUM(pages),0) as pgs FROM print_jobs WHERE DATE(created_at)=:d");
$stQ->execute([':d' => $today]);
$stats    = $stQ->fetch();
$today_rev   = floatval($stats['rev']);
$today_pages = intval($stats['pgs']);

$logs = $pdo->query("SELECT * FROM print_jobs ORDER BY created_at DESC LIMIT 15")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>The Desktop | Printing</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="includes/navbar.css">
<style>
html{overflow-y:scroll;}
body{background-color:#050b14;background-image:linear-gradient(rgba(19,39,66,.3) 1px,transparent 1px),linear-gradient(90deg,rgba(19,39,66,.3) 1px,transparent 1px);background-size:50px 50px;color:white;font-family:'Segoe UI',sans-serif;margin:0;min-height:100vh;}
.main-container{max-width:1400px;margin:0 auto;padding:36px 40px;display:flex;gap:28px;}
.left-col{flex:2;}.right-col{flex:1;}
.panel-card{background:rgba(10,25,47,.85);border:1px solid #132742;border-radius:12px;padding:26px;}
.stat-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:20px;}
.stat-box{background:rgba(10,25,47,.85);border:1px solid #132742;padding:18px;border-radius:12px;text-align:center;}
.stat-val{display:block;font-size:20px;font-weight:bold;color:#2ecc71;}
.stat-lbl{font-size:10px;color:#8aa0c5;text-transform:uppercase;letter-spacing:1px;}
table{width:100%;border-collapse:collapse;margin-top:10px;}
th{text-align:left;color:#8aa0c5;font-size:11px;border-bottom:1px solid #132742;padding:11px 12px;text-transform:uppercase;letter-spacing:1px;}
td{padding:14px 12px;font-size:14px;border-bottom:1px solid #132742;}
.type-badge{background:#132742;padding:3px 9px;border-radius:4px;font-size:11px;}
.price-text{color:#2ecc71;font-weight:bold;}
.void-btn{color:#ff4d4d;background:none;border:none;cursor:pointer;transition:.2s;font-size:15px;}
.void-btn:hover{transform:scale(1.15);}
label{display:block;font-size:12px;color:#8aa0c5;margin:14px 0 6px;}
input{width:100%;padding:11px;background:#050b14;border:1px solid #132742;color:white;border-radius:8px;box-sizing:border-box;}
.toggle-row{display:flex;background:#050b14;border-radius:8px;padding:4px;border:1px solid #132742;}
.toggle-btn{flex:1;border:none;background:none;color:#8aa0c5;padding:9px;cursor:pointer;border-radius:6px;font-weight:bold;transition:.25s;}
.toggle-btn.active{background:#38bdf8;color:white;}
.confirm-btn{width:100%;background:#2ecc71;color:white;border:none;padding:14px;border-radius:10px;font-weight:bold;cursor:pointer;margin-top:18px;transition:.2s;}
.confirm-btn:hover{background:#27ae60;}
.price-preview{background:#050b14;padding:14px;border-radius:10px;margin-top:16px;display:flex;justify-content:space-between;border:1px solid #132742;}
#voidModal{display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.85);backdrop-filter:blur(5px);z-index:9999;align-items:center;justify-content:center;}
.void-box{background:#0a192f;border:1px solid #ff4d4d;box-shadow:0 0 24px rgba(255,77,77,.2);border-radius:14px;padding:30px;width:400px;text-align:center;}
.void-box i{color:#ff4d4d;font-size:44px;margin-bottom:14px;display:block;}
.void-box h3{margin:0 0 8px;}.void-box p{color:#8aa0c5;font-size:14px;margin-bottom:24px;}
.void-actions{display:flex;gap:12px;}
.btn-cancel{flex:1;background:#1e293b;color:white;border:none;padding:12px;border-radius:8px;cursor:pointer;font-weight:bold;}
.btn-confirm{flex:1;background:#ff4d4d;color:white;border:none;padding:12px;border-radius:8px;cursor:pointer;font-weight:bold;}
</style>
</head>
<body>
<?php include 'includes/navbar.php'; ?>
<div class="main-container">
    <div class="left-col">
        <h2 style="margin:0 0 4px;font-size:22px;">Printing Management</h2>
        <p style="color:#8aa0c5;font-size:13px;margin:0 0 24px;">Record and track customer print jobs</p>
        <div class="panel-card">
            <h3 style="color:#38bdf8;margin:0 0 18px;font-size:15px;">Recent Print Logs</h3>
            <table>
                <thead><tr><th>Type</th><th>Pages</th><th>Price</th><th>Time</th><th>Action</th></tr></thead>
                <tbody>
                <?php foreach($logs as $log): ?>
                <tr>
                    <td><span class="type-badge"><?= htmlspecialchars($log['type']) ?></span></td>
                    <td><?= $log['pages'] ?> Pages</td>
                    <td class="price-text">₱<?= number_format($log['price'],2) ?></td>
                    <td style="color:#8aa0c5;"><?= date('g:i A',strtotime($log['created_at'])) ?></td>
                    <td><button class="void-btn" onclick="voidPrint(<?= $log['id'] ?>)"><i class="fas fa-trash-alt"></i></button></td>
                </tr>
                <?php endforeach; ?>
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
let currentType='BW';
const bwRate=<?= $db_bw_rate ?>,colorRate=<?= $db_color_rate ?>;
function setType(type,btn){currentType=type;document.getElementById('print_type').value=type;document.querySelectorAll('.toggle-btn').forEach(b=>b.classList.remove('active'));btn.classList.add('active');calculateTotal();}
function calculateTotal(){const pages=document.getElementById('pages').value||0;document.getElementById('display_total').innerText='₱'+(pages*(currentType==='BW'?bwRate:colorRate)).toFixed(2);}
window.onload=calculateTotal;
let targetVoidId=null;
function voidPrint(id){targetVoidId=id;document.getElementById('voidModal').style.display='flex';document.getElementById('confirmVoidBtn').onclick=()=>{window.location.href='void_print.php?id='+targetVoidId;};}
function closeVoidModal(){document.getElementById('voidModal').style.display='none';}
window.onclick=e=>{if(e.target==document.getElementById('voidModal'))closeVoidModal();}
</script>
</body></html>

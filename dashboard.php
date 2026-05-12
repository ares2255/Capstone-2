<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['admin_username']) && !isset($_SESSION['username'])) {
    header("Location: index.php"); exit();
}

$is_admin     = isset($_SESSION['admin_username']);
$display_user = $is_admin ? $_SESSION['admin_username'] : $_SESSION['username'];
$current_page = 'dashboard';

$selected_date = isset($_GET['date']) && !empty($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$is_today      = ($selected_date === date('Y-m-d'));
$date_label    = $is_today ? "Today" : date('F j, Y', strtotime($selected_date));

try {
    $active = $pdo->query("SELECT COUNT(*) FROM pcs WHERE status='active'")->fetchColumn();

    $q = $pdo->prepare("SELECT COALESCE(SUM(cost),0) FROM sessions WHERE DATE(end_time)=:d");
    $q->execute([':d'=>$selected_date]); $pc_revenue = $q->fetchColumn();

    $q = $pdo->prepare("SELECT COALESCE(SUM(price),0) FROM print_jobs WHERE DATE(created_at)=:d");
    $q->execute([':d'=>$selected_date]); $print_revenue = $q->fetchColumn();

    $total_combined_revenue = $pc_revenue + $print_revenue;

    $q = $pdo->prepare("SELECT COUNT(*) FROM sessions WHERE DATE(start_time)=:d");
    $q->execute([':d'=>$selected_date]); $sessions = $q->fetchColumn();

    $q = $pdo->prepare("SELECT COUNT(*) FROM print_jobs WHERE DATE(created_at)=:d");
    $q->execute([':d'=>$selected_date]); $prints = $q->fetchColumn();

    // PostgreSQL-compatible UNION query — no CONCAT with cast
    $histQ = $pdo->prepare("
        SELECT 'Session' as type, s.id as trans_id, p.name as description,
               s.cost as price, s.end_time as date
        FROM sessions s JOIN pcs p ON p.id = s.pc_id
        WHERE DATE(s.end_time) = :d1
           OR (s.end_time IS NULL AND DATE(s.start_time) = :d2)
        UNION ALL
        SELECT 'Print' as type, pj.id as trans_id,
               pj.type || ' print - ' || pj.pages::text || ' pages' as description,
               pj.price, pj.created_at as date
        FROM print_jobs pj
        WHERE DATE(pj.created_at) = :d3
        ORDER BY date DESC
    ");
    $histQ->execute([':d1'=>$selected_date, ':d2'=>$selected_date, ':d3'=>$selected_date]);
    $history   = $histQ->fetchAll();
    $row_count = count($history);

} catch (PDOException $e) {
    die("<b>Query Error:</b> " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>The Desktop | Dashboard</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="includes/navbar.css">
<style>
html{overflow-y:scroll;scrollbar-gutter:stable;}
body{background:linear-gradient(135deg,#0d1117 0%,#1a1a2e 50%,#16213e 100%);color:white;font-family:'Inter',sans-serif;margin:0;min-height:100vh;}
.main-container{max-width:1400px;margin:0 auto;padding:36px 40px;}
.page-header{display:flex;align-items:flex-end;justify-content:space-between;margin-bottom:28px;flex-wrap:wrap;gap:16px;}
.page-header h2{margin:0;font-size:22px;font-weight:700;letter-spacing:-.3px;}
.page-header p{color:#8aa0c5;font-size:13px;margin:4px 0 0;}
.date-bar{display:flex;align-items:center;gap:10px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);border-radius:12px;padding:10px 16px;}
.date-bar label{color:#8aa0c5;font-size:12px;text-transform:uppercase;letter-spacing:1px;white-space:nowrap;}
.date-bar input[type="date"]{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);color:white;padding:7px 12px;border-radius:8px;font-size:13px;cursor:pointer;outline:none;}
.date-bar input[type="date"]::-webkit-calendar-picker-indicator{filter:invert(1);cursor:pointer;}
.btn-view{background:#1e2a78;color:white;border:none;padding:7px 16px;border-radius:8px;font-size:13px;font-weight:700;cursor:pointer;white-space:nowrap;transition:.2s;}
.btn-view:hover{background:#2d3eaa;}
.btn-today{background:transparent;color:#8aa0c5;border:1px solid rgba(255,255,255,.1);padding:7px 14px;border-radius:8px;font-size:12px;cursor:pointer;text-decoration:none;display:flex;align-items:center;gap:5px;transition:.2s;}
.btn-today:hover{border-color:#1e2a78;color:white;}
.date-banner{display:flex;align-items:center;gap:10px;background:rgba(74,108,247,.08);border:1px solid rgba(74,108,247,.2);border-radius:10px;padding:10px 16px;margin-bottom:24px;font-size:13px;color:#7b9cff;}
.date-banner.today{background:rgba(46,204,113,.07);border-color:rgba(46,204,113,.2);color:#2ecc71;}
.stat-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:20px;margin-bottom:36px;}
.stat-card{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);padding:24px;border-radius:16px;border-bottom:4px solid #1e2a78;transition:transform .2s,box-shadow .2s;}
.stat-card:hover{transform:translateY(-2px);box-shadow:0 8px 32px rgba(74,108,247,.15);}
.stat-card h2{margin:0 0 6px;font-size:28px;font-weight:700;}
.stat-card small{color:#8aa0c5;text-transform:uppercase;font-size:10px;letter-spacing:1px;}
.table-container{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);border-radius:16px;padding:28px;}
.table-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:22px;}
.table-header h3{margin:0;font-size:15px;color:#7b9cff;}
.record-count{background:rgba(255,255,255,.08);color:#8aa0c5;font-size:11px;padding:4px 10px;border-radius:20px;}
table{width:100%;border-collapse:collapse;font-size:14px;}
th{text-align:left;color:#8aa0c5;border-bottom:1px solid rgba(255,255,255,.08);padding:12px 0;font-weight:600;text-transform:uppercase;font-size:11px;letter-spacing:1.5px;}
td{padding:18px 0;border-bottom:1px solid rgba(255,255,255,.06);color:#cbd5e1;vertical-align:middle;}
td:first-child,th:first-child{padding-left:4px;width:90px;}
td:last-child,th:last-child{width:60px;text-align:center;}
.no-records{text-align:center;padding:50px 0;color:#8aa0c5;font-size:14px;}
.no-records i{font-size:36px;display:block;margin-bottom:12px;}
.trash-btn{color:#ff4d4d;cursor:pointer;opacity:.7;transition:.2s;font-size:16px;}
.trash-btn:hover{opacity:1;transform:scale(1.15);}
.custom-modal{position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.8);backdrop-filter:blur(10px);display:flex;align-items:center;justify-content:center;z-index:2000;}
.modal-content{background:#ffffff;border-radius:24px;padding:36px;text-align:center;max-width:400px;width:90%;border-bottom:8px solid #1e2a78;box-shadow:0 20px 60px rgba(0,0,0,.5);color:#1e293b;}
.modal-icon{color:#ff4d4d;font-size:48px;margin-bottom:18px;}
.modal-content h2{color:#1e293b;margin:0 0 10px;}
.modal-content p{color:#64748b;}
.btn-cancel{background:#f1f5f9;color:#64748b;border:1px solid #e2e8f0;padding:12px 25px;border-radius:8px;cursor:pointer;flex:1;font-weight:600;transition:.2s;}
.btn-cancel:hover{background:#e2e8f0;}
.btn-confirm{background:#ff4d4d;color:white;border:none;padding:12px 25px;border-radius:8px;cursor:pointer;font-weight:bold;flex:1;transition:.2s;}
.btn-confirm:hover{background:#e03030;}
</style>
</head>
<body>
<?php include 'includes/navbar.php'; ?>
<div class="main-container">

    <div class="page-header">
        <div>
            <h2><?= $is_admin ? 'System Overview' : 'Staff Dashboard' ?></h2>
            <p>Sales history and transaction logs</p>
        </div>
        <form method="GET" action="dashboard.php">
            <div class="date-bar">
                <label><i class="fas fa-calendar-alt"></i>&nbsp; View Date</label>
                <input type="date" name="date" value="<?= htmlspecialchars($selected_date) ?>" max="<?= date('Y-m-d') ?>">
                <button type="submit" class="btn-view"><i class="fas fa-search"></i> View</button>
                <?php if(!$is_today): ?>
                    <a href="dashboard.php" class="btn-today"><i class="fas fa-undo"></i> Today</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="date-banner <?= $is_today ? 'today' : '' ?>">
        <i class="fas fa-<?= $is_today ? 'circle' : 'calendar-day' ?>"></i>
        <?php if($is_today): ?>
            Showing <strong>today's</strong> transactions — <?= date('F j, Y') ?>
        <?php else: ?>
            Showing transactions for <strong><?= $date_label ?></strong>
        <?php endif; ?>
    </div>

    <div class="stat-grid">
        <div class="stat-card"><h2 style="color:#2ecc71;">₱<?= number_format($total_combined_revenue,2) ?></h2><small><?= $date_label ?>'s Revenue</small></div>
        <div class="stat-card"><h2 style="color:#f1c40f;"><?= $sessions ?></h2><small>Sessions</small></div>
        <div class="stat-card"><h2 style="color:#a855f7;"><?= $prints ?></h2><small>Print Jobs</small></div>
        <div class="stat-card"><h2 style="color:#1fb6ff;"><?= $active ?></h2><small>Active PCs Now</small></div>
    </div>

    <div class="table-container">
        <div class="table-header">
            <h3><i class="fas fa-receipt"></i> Transaction History — <?= $date_label ?></h3>
            <span class="record-count"><?= $row_count ?> record<?= $row_count != 1 ? 's' : '' ?></span>
        </div>
        <?php if($row_count === 0): ?>
            <div class="no-records"><i class="fas fa-folder-open"></i><br>No transactions found for <?= $date_label ?>.</div>
        <?php else: ?>
        <table>
            <thead><tr><th>TYPE</th><th>DESCRIPTION</th><th>AMOUNT</th><th>TIME</th><th>DEL</th></tr></thead>
            <tbody>
            <?php foreach($history as $row): ?>
            <tr>
                <td><span style="border:1px solid currentColor;padding:3px 9px;border-radius:6px;font-size:11px;color:<?= $row['type']==='Session'?'#38bdf8':'#a855f7' ?>;"><?= $row['type'] ?></span></td>
                <td><?= htmlspecialchars($row['description']) ?></td>
                <td style="color:#2ecc71;font-weight:bold;font-family:monospace;font-size:16px;">₱<?= number_format((float)($row['price'] ?? 0),2) ?></td>
                <td style="color:#8aa0c5;font-size:13px;"><?= $row['date'] ? date('g:i A',strtotime($row['date'])) : '<span style="color:#ff4d4d">Active</span>' ?></td>
                <td style="text-align:center;">
                    <?php if($row['date']): ?>
                    <i class="fas fa-trash-alt trash-btn" onclick="confirmVoid('<?= $row['trans_id'] ?>','<?= $row['type'] ?>')"></i>
                    <?php else: ?><span style="color:#4a5f7a">—</span><?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<div id="voidModal" class="custom-modal" style="display:none;">
    <div class="modal-content">
        <div class="modal-icon"><i class="fas fa-exclamation-triangle"></i></div>
        <h2>Void Transaction?</h2>
        <p style="color:#94a3b8;font-size:14px;">This will remove the record and deduct it from daily revenue.</p>
        <div style="display:flex;gap:15px;margin-top:22px;">
            <button class="btn-cancel" onclick="closeVoidModal()">Cancel</button>
            <button class="btn-confirm" id="confirmVoidBtn">Yes, Void it</button>
        </div>
    </div>
</div>
<script>
function confirmVoid(id,type){
    document.getElementById('voidModal').style.display='flex';
    document.getElementById('confirmVoidBtn').onclick=function(){window.location.href='process_void.php?id='+id+'&type='+type;};
}
function closeVoidModal(){document.getElementById('voidModal').style.display='none';}
window.onclick=function(e){if(e.target==document.getElementById('voidModal'))closeVoidModal();}
<?php if($is_today): ?>setTimeout(()=>{location.reload();},30000);<?php endif; ?>
</script>
</body></html>

<?php
session_start();
include "config/db.php";
date_default_timezone_set('Asia/Manila');

if (!isset($_SESSION['admin_username']) && !isset($_SESSION['username'])) {
    header("Location: index.php"); exit();
}

$period = $_GET['period'] ?? 'daily';
if (!in_array($period, ['daily','weekly','monthly'])) { $period = 'daily'; }

$today = date('Y-m-d');

// ── Work out the date range for the chosen period ──
if ($period === 'daily') {
    $range_start = $today;
    $range_end   = $today;
    $period_label = 'Daily Sales Report';
    $range_label  = date('F j, Y', strtotime($range_start));
} elseif ($period === 'weekly') {
    // Current calendar week, Monday through today (or Sunday if the week has already passed)
    $range_start = date('Y-m-d', strtotime('monday this week'));
    $range_end   = $today;
    $period_label = 'Weekly Sales Report';
    $range_label  = date('M j', strtotime($range_start)) . ' – ' . date('M j, Y', strtotime($range_end));
} else { // monthly
    $range_start = date('Y-m-01');
    $range_end   = $today;
    $period_label = 'Monthly Sales Report';
    $range_label  = date('F Y', strtotime($range_start));
}

try {
    // ── PC Sessions ──
    $q = $pdo->prepare("SELECT COUNT(*) as cnt, COALESCE(SUM(cost),0) as total
                         FROM sessions
                         WHERE end_time IS NOT NULL AND DATE(end_time) BETWEEN :s AND :e");
    $q->execute([':s'=>$range_start, ':e'=>$range_end]);
    $sess = $q->fetch();
    $session_count = (int)$sess['cnt'];
    $session_total = (float)$sess['total'];

    // ── Printing (BW / Color, excludes scans) ──
    $q = $pdo->prepare("SELECT COUNT(*) as cnt, COALESCE(SUM(pages),0) as pages, COALESCE(SUM(price),0) as total
                         FROM print_jobs
                         WHERE type != 'SCAN' AND DATE(created_at) BETWEEN :s AND :e");
    $q->execute([':s'=>$range_start, ':e'=>$range_end]);
    $print = $q->fetch();
    $print_count = (int)$print['cnt'];
    $print_pages = (int)$print['pages'];
    $print_total = (float)$print['total'];

    // ── Scanning ──
    $q = $pdo->prepare("SELECT COUNT(*) as cnt, COALESCE(SUM(pages),0) as pages, COALESCE(SUM(price),0) as total
                         FROM print_jobs
                         WHERE type = 'SCAN' AND DATE(created_at) BETWEEN :s AND :e");
    $q->execute([':s'=>$range_start, ':e'=>$range_end]);
    $scan = $q->fetch();
    $scan_count = (int)$scan['cnt'];
    $scan_pages = (int)$scan['pages'];
    $scan_total = (float)$scan['total'];

    // ── Per-PC breakdown (for the period) ──
    $q = $pdo->prepare("SELECT p.name, COUNT(s.id) as cnt, COALESCE(SUM(s.cost),0) as total
                         FROM pcs p
                         JOIN sessions s ON s.pc_id = p.id AND s.end_time IS NOT NULL AND DATE(s.end_time) BETWEEN :s AND :e
                         GROUP BY p.name
                         HAVING COUNT(s.id) > 0
                         ORDER BY total DESC");
    $q->execute([':s'=>$range_start, ':e'=>$range_end]);
    $per_pc = $q->fetchAll();

    $grand_total = $session_total + $print_total + $scan_total;

} catch (PDOException $e) {
    die("<b>Query Error:</b> " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Q-Solutions | <?= htmlspecialchars($period_label) ?></title>
<link rel="icon" type="image/jpeg" href="q.jpg">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
*{box-sizing:border-box;}
body{
    font-family:'Courier New',monospace;
    background:#e5e7eb;
    margin:0;
    padding:24px 0;
    color:#111;
}
.receipt{
    background:#fff;
    width:340px;
    margin:0 auto;
    padding:22px 20px 30px;
    box-shadow:0 4px 20px rgba(0,0,0,.15);
}
.center{text-align:center;}
.logo{height:48px;width:auto;display:block;margin:0 auto 8px;object-fit:contain;}
h1{font-size:16px;margin:0 0 2px;letter-spacing:1px;}
.tagline{font-size:10px;color:#555;margin:0 0 12px;}
.divider{border-top:1px dashed #333;margin:10px 0;}
.divider.thick{border-top:2px solid #333;}
.row{display:flex;justify-content:space-between;font-size:12px;margin:3px 0;}
.row .label{color:#333;}
.row .value{font-weight:bold;}
.section-title{font-size:12px;font-weight:bold;text-transform:uppercase;letter-spacing:1px;margin:14px 0 6px;}
.sub-row{display:flex;justify-content:space-between;font-size:11px;color:#444;margin:2px 0 2px 8px;}
.grand{font-size:15px;font-weight:bold;margin-top:8px;}
.meta{font-size:10px;color:#666;margin:2px 0;}
.footer{font-size:10px;color:#666;text-align:center;margin-top:16px;}
.no-print{max-width:340px;margin:16px auto 0;display:flex;gap:10px;justify-content:center;}
.no-print button{
    font-family:'Inter',sans-serif;font-size:13px;font-weight:600;
    padding:9px 18px;border-radius:8px;border:none;cursor:pointer;
}
.btn-print{background:#1e2a78;color:#fff;}
.btn-close{background:#e2e8f0;color:#334155;}
@media print{
    body{background:#fff;padding:0;}
    .receipt{box-shadow:none;width:100%;}
    .no-print{display:none;}
}
</style>
</head>
<body>
<div class="receipt">
    <div class="center">
        <img src="logo.jpg" class="logo" alt="Q Solutions">
        <h1>Q-SOLUTIONS</h1>
        <div class="tagline">Internet Cafe &amp; Printing Services</div>
    </div>
    <div class="divider thick"></div>
    <div class="center" style="font-size:13px;font-weight:bold;margin:6px 0 2px;"><?= htmlspecialchars($period_label) ?></div>
    <div class="center meta"><?= htmlspecialchars($range_label) ?></div>
    <div class="center meta">Printed: <?= date('M j, Y g:i A') ?></div>
    <div class="divider"></div>

    <div class="section-title">PC Sessions</div>
    <div class="row"><span class="label">Sessions Completed</span><span class="value"><?= $session_count ?></span></div>
    <div class="row"><span class="label">Sessions Revenue</span><span class="value">₱<?= number_format($session_total,2) ?></span></div>
    <?php if(!empty($per_pc)): foreach($per_pc as $pc): ?>
    <div class="sub-row"><span><?= htmlspecialchars($pc['name']) ?> (<?= $pc['cnt'] ?>)</span><span>₱<?= number_format($pc['total'],2) ?></span></div>
    <?php endforeach; endif; ?>

    <div class="divider"></div>
    <div class="section-title">Printing</div>
    <div class="row"><span class="label">Print Jobs</span><span class="value"><?= $print_count ?></span></div>
    <div class="row"><span class="label">Pages Printed</span><span class="value"><?= $print_pages ?></span></div>
    <div class="row"><span class="label">Printing Revenue</span><span class="value">₱<?= number_format($print_total,2) ?></span></div>

    <div class="divider"></div>
    <div class="section-title">Scanning</div>
    <div class="row"><span class="label">Scan Jobs</span><span class="value"><?= $scan_count ?></span></div>
    <div class="row"><span class="label">Pages Scanned</span><span class="value"><?= $scan_pages ?></span></div>
    <div class="row"><span class="label">Scanning Revenue</span><span class="value">₱<?= number_format($scan_total,2) ?></span></div>

    <div class="divider thick"></div>
    <div class="row grand"><span>GRAND TOTAL</span><span>₱<?= number_format($grand_total,2) ?></span></div>
    <div class="divider"></div>

    <div class="footer">
        Sessions + Printing + Scanning<br>
        Thank you!
    </div>
</div>

<div class="no-print">
    <button class="btn-print" onclick="window.print()"><i class="fas fa-print"></i> Print</button>
    <button class="btn-close" onclick="window.close()">Close</button>
</div>

<script>
    window.addEventListener('load', function(){
        setTimeout(function(){ window.print(); }, 300);
    });
</script>
</body>
</html>

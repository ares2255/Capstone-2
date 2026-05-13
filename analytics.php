<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['admin_username']) && !isset($_SESSION['username'])) {
    header("Location: index.php"); exit();
}

$is_admin = isset($_SESSION['admin_username']);
$display_user = $is_admin ? $_SESSION['admin_username'] : $_SESSION['username'];

try {
    $today = date('Y-m-d');
    $yesterday = date('Y-m-d', strtotime('-1 day'));

    // Today
    $q = $pdo->prepare("SELECT COALESCE(SUM(cost),0) FROM sessions WHERE DATE(end_time)=:d");
    $q->execute([':d'=>$today]); $today_session = $q->fetchColumn();
    $q = $pdo->prepare("SELECT COALESCE(SUM(price),0) FROM print_jobs WHERE DATE(created_at)=:d");
    $q->execute([':d'=>$today]); $today_print = $q->fetchColumn();
    $today_total = $today_session + $today_print;

    // Yesterday
    $q = $pdo->prepare("SELECT COALESCE(SUM(cost),0) FROM sessions WHERE DATE(end_time)=:d");
    $q->execute([':d'=>$yesterday]); $yest_session = $q->fetchColumn();
    $q = $pdo->prepare("SELECT COALESCE(SUM(price),0) FROM print_jobs WHERE DATE(created_at)=:d");
    $q->execute([':d'=>$yesterday]); $yest_print = $q->fetchColumn();
    $yest_total = $yest_session + $yest_print;

    $diff = $today_total - $yest_total;
    $pct = $yest_total > 0 ? round(($diff/$yest_total)*100,1) : 0;

    // All time
    $total_all  = $pdo->query("SELECT COALESCE(SUM(cost),0) FROM sessions WHERE end_time IS NOT NULL")->fetchColumn();
    $total_all += $pdo->query("SELECT COALESCE(SUM(price),0) FROM print_jobs")->fetchColumn();

    // Last 7 days
    $weekly = $pdo->query("
        SELECT ds::date as day,
               COALESCE((SELECT SUM(cost) FROM sessions WHERE DATE(end_time)=ds::date),0) as session_rev,
               COALESCE((SELECT SUM(price) FROM print_jobs WHERE DATE(created_at)=ds::date),0) as print_rev
        FROM generate_series(CURRENT_DATE-INTERVAL '6 days', CURRENT_DATE, '1 day') ds
        ORDER BY day ASC
    ")->fetchAll();

    // Last 6 months
    $monthly = $pdo->query("
        SELECT TO_CHAR(ms,'Mon YYYY') as month,
               COALESCE((SELECT SUM(cost) FROM sessions WHERE DATE_TRUNC('month',end_time)=ms),0)+
               COALESCE((SELECT SUM(price) FROM print_jobs WHERE DATE_TRUNC('month',created_at)=ms),0) as total
        FROM generate_series(DATE_TRUNC('month',CURRENT_DATE-INTERVAL '5 months'), DATE_TRUNC('month',CURRENT_DATE),'1 month') ms
        ORDER BY ms ASC
    ")->fetchAll();

    // Top PCs
    $top_pcs = $pdo->query("
        SELECT p.name, COALESCE(SUM(s.cost),0) as total
        FROM pcs p LEFT JOIN sessions s ON s.pc_id=p.id
        GROUP BY p.name ORDER BY total DESC LIMIT 5
    ")->fetchAll();

} catch(PDOException $e){ die("<b>Error:</b> ".$e->getMessage()); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Q-Solutions | Analytics</title>
<link rel="icon" type="image/jpeg" href="q.jpg">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="includes/navbar.css">
<script>(function(){if(localStorage.getItem("settings_theme")==="light"){document.documentElement.classList.add("light-mode");}})()</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
html{overflow-y:scroll;scrollbar-gutter:stable;}
body{background:linear-gradient(135deg,#0d1117 0%,#1a1a2e 50%,#16213e 100%);color:white;font-family:'Inter',sans-serif;margin:0;min-height:100vh;}
.main-container{max-width:1400px;margin:0 auto;padding:36px 40px;}
.page-header{margin-bottom:28px;}
.page-header h2{margin:0 0 4px;font-size:22px;font-weight:700;}
.page-header p{color:#8aa0c5;font-size:13px;margin:0;}
.stat-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:20px;margin-bottom:28px;}
.stat-card{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);padding:24px;border-radius:16px;border-bottom:4px solid #1e2a78;transition:transform .2s,box-shadow .2s;}
.stat-card:hover{transform:translateY(-2px);box-shadow:0 8px 32px rgba(74,108,247,.15);}
.stat-card h2{margin:0 0 6px;font-size:26px;font-weight:700;}
.stat-card small{color:#8aa0c5;text-transform:uppercase;font-size:10px;letter-spacing:1px;}
.stat-card .badge{font-size:11px;padding:3px 8px;border-radius:20px;margin-left:8px;}
.up{background:rgba(46,204,113,.15);color:#2ecc71;}
.down{background:rgba(255,77,77,.15);color:#ff4d4d;}
.chart-grid{display:grid;grid-template-columns:2fr 1fr;gap:20px;margin-bottom:20px;}
.chart-grid-bottom{display:grid;grid-template-columns:1fr 1fr;gap:20px;}
.chart-card{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);border-radius:16px;padding:24px;}
.chart-card h3{margin:0 0 20px;font-size:14px;color:#7b9cff;display:flex;align-items:center;gap:8px;}
.date-filter{display:flex;align-items:center;gap:10px;margin-bottom:28px;flex-wrap:wrap;}
.date-filter label{color:#8aa0c5;font-size:12px;text-transform:uppercase;letter-spacing:1px;}
.date-filter input{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);color:white;padding:8px 12px;border-radius:8px;font-size:13px;outline:none;}
.date-filter input::-webkit-calendar-picker-indicator{filter:invert(1);}
.btn-view{background:#1e2a78;color:white;border:none;padding:8px 18px;border-radius:8px;font-size:13px;font-weight:700;cursor:pointer;transition:.2s;}
.btn-view:hover{background:#2d3eaa;}
.compare-box{display:flex;gap:16px;}
.compare-item{flex:1;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);border-radius:16px;padding:20px;text-align:center;}
.compare-item .label{color:#8aa0c5;font-size:11px;text-transform:uppercase;letter-spacing:1px;margin-bottom:8px;}
.compare-item .amount{font-size:24px;font-weight:bold;}
.compare-item.today-box .amount{color:#2ecc71;}
.compare-item.yest-box .amount{color:#f1c40f;}
.vs-divider{display:flex;align-items:center;justify-content:center;color:#4a5f7a;font-size:18px;font-weight:bold;}
</style>
</head>
<body>
<?php include 'includes/navbar.php'; ?>
<div class="main-container">

    <div class="page-header">
        <h2><i class="fas fa-chart-line" style="color:#38bdf8;margin-right:10px;"></i>Analytics</h2>
        <p>Revenue breakdown, trends, and performance overview</p>
    </div>

    <!-- Stat Cards -->
    <div class="stat-grid">
        <div class="stat-card">
            <h2 style="color:#2ecc71;">₱<?= number_format($today_total,2) ?></h2>
            <small>Today's Revenue
                <?php if($pct != 0): ?>
                <span class="badge <?= $pct>0?'up':'down' ?>"><?= $pct>0?'↑':'↓' ?> <?= abs($pct) ?>%</span>
                <?php endif; ?>
            </small>
        </div>
        <div class="stat-card">
            <h2 style="color:#f1c40f;">₱<?= number_format($yest_total,2) ?></h2>
            <small>Yesterday's Revenue</small>
        </div>
        <div class="stat-card">
            <h2 style="color:#a855f7;">₱<?= number_format($diff,2) ?></h2>
            <small><?= $diff>=0?'Gained vs Yesterday':'Lost vs Yesterday' ?></small>
        </div>
        <div class="stat-card">
            <h2 style="color:#1fb6ff;">₱<?= number_format($total_all,2) ?></h2>
            <small>All Time Revenue</small>
        </div>
    </div>

    <!-- Today vs Yesterday -->
    <div style="margin-bottom:28px;">
        <div class="chart-card">
            <h3><i class="fas fa-balance-scale"></i> Today vs Yesterday</h3>
            <div class="compare-box">
                <div class="compare-item yest-box">
                    <div class="label">Yesterday</div>
                    <div class="amount">₱<?= number_format($yest_total,2) ?></div>
                    <div style="color:#8aa0c5;font-size:12px;margin-top:6px;">
                        Sessions: ₱<?= number_format($yest_session,2) ?> &nbsp;|&nbsp; Printing: ₱<?= number_format($yest_print,2) ?>
                    </div>
                </div>
                <div class="vs-divider">VS</div>
                <div class="compare-item today-box">
                    <div class="label">Today</div>
                    <div class="amount">₱<?= number_format($today_total,2) ?></div>
                    <div style="color:#8aa0c5;font-size:12px;margin-top:6px;">
                        Sessions: ₱<?= number_format($today_session,2) ?> &nbsp;|&nbsp; Printing: ₱<?= number_format($today_print,2) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Weekly Chart + Top PCs -->
    <div class="chart-grid">
        <div class="chart-card">
            <h3><i class="fas fa-chart-bar"></i> Last 7 Days Revenue</h3>
            <canvas id="weeklyChart" height="120"></canvas>
        </div>
        <div class="chart-card">
            <h3><i class="fas fa-desktop"></i> Top Earning PCs</h3>
            <canvas id="pcChart" height="120"></canvas>
        </div>
    </div>

    <!-- Monthly Chart + Custom Date -->
    <div class="chart-grid-bottom">
        <div class="chart-card">
            <h3><i class="fas fa-calendar-alt"></i> Monthly Revenue (Last 6 Months)</h3>
            <canvas id="monthlyChart" height="130"></canvas>
        </div>
        <div class="chart-card">
            <h3><i class="fas fa-search"></i> Check Any Date</h3>
            <div class="date-filter">
                <input type="date" id="customDate" value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>">
                <button class="btn-view" onclick="loadDate()"><i class="fas fa-search"></i> View</button>
            </div>
            <div id="customResult" style="margin-top:10px;">
                <div style="color:#8aa0c5;text-align:center;padding:20px;">Select a date and click View</div>
            </div>
        </div>
    </div>

</div>

<script>
const chartDefaults = {
    color: 'rgba(255,255,255,0.7)',
    grid: 'rgba(255,255,255,0.05)'
};

// Weekly Chart
new Chart(document.getElementById('weeklyChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_map(fn($r)=>date('D m/d',strtotime($r['day'])), $weekly)) ?>,
        datasets: [
            {label:'Sessions',data:<?= json_encode(array_map(fn($r)=>(float)$r['session_rev'],$weekly)) ?>,backgroundColor:'rgba(56,189,248,0.7)',borderRadius:6},
            {label:'Printing',data:<?= json_encode(array_map(fn($r)=>(float)$r['print_rev'],$weekly)) ?>,backgroundColor:'rgba(168,85,247,0.7)',borderRadius:6}
        ]
    },
    options:{responsive:true,plugins:{legend:{labels:{color:'#8aa0c5'}}},scales:{x:{ticks:{color:'#8aa0c5'},grid:{color:chartDefaults.grid}},y:{ticks:{color:'#8aa0c5',callback:v=>'₱'+v},grid:{color:chartDefaults.grid}}}}
});

// Monthly Chart
new Chart(document.getElementById('monthlyChart'), {
    type: 'line',
    data: {
        labels: <?= json_encode(array_column($monthly,'month')) ?>,
        datasets: [{
            label:'Monthly Revenue',
            data:<?= json_encode(array_map(fn($r)=>(float)$r['total'],$monthly)) ?>,
            borderColor:'#2ecc71',backgroundColor:'rgba(46,204,113,0.1)',fill:true,tension:0.4,pointBackgroundColor:'#2ecc71'
        }]
    },
    options:{responsive:true,plugins:{legend:{labels:{color:'#8aa0c5'}}},scales:{x:{ticks:{color:'#8aa0c5'},grid:{color:chartDefaults.grid}},y:{ticks:{color:'#8aa0c5',callback:v=>'₱'+v},grid:{color:chartDefaults.grid}}}}
});

// Top PCs Chart
new Chart(document.getElementById('pcChart'), {
    type: 'doughnut',
    data: {
        labels: <?= json_encode(array_column($top_pcs,'name')) ?>,
        datasets: [{data:<?= json_encode(array_map(fn($r)=>(float)$r['total'],$top_pcs)) ?>,backgroundColor:['#38bdf8','#2ecc71','#f1c40f','#a855f7','#ff4d4d']}]
    },
    options:{responsive:true,plugins:{legend:{labels:{color:'#8aa0c5'}}}}
});

// Custom date lookup
function loadDate() {
    const date = document.getElementById('customDate').value;
    if (!date) return;
    document.getElementById('customResult').innerHTML = '<div style="color:#8aa0c5;text-align:center;padding:20px;"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
    fetch('analytics_data.php?date='+date)
        .then(r=>r.json())
        .then(d=>{
            document.getElementById('customResult').innerHTML = `
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:10px;">
                    <div style="background:rgba(56,189,248,.08);border:1px solid rgba(56,189,248,.2);border-radius:10px;padding:16px;text-align:center;">
                        <div style="color:#8aa0c5;font-size:11px;text-transform:uppercase;margin-bottom:6px;">Sessions</div>
                        <div style="color:#38bdf8;font-size:20px;font-weight:bold;">₱${parseFloat(d.session).toFixed(2)}</div>
                    </div>
                    <div style="background:rgba(168,85,247,.08);border:1px solid rgba(168,85,247,.2);border-radius:10px;padding:16px;text-align:center;">
                        <div style="color:#8aa0c5;font-size:11px;text-transform:uppercase;margin-bottom:6px;">Printing</div>
                        <div style="color:#a855f7;font-size:20px;font-weight:bold;">₱${parseFloat(d.print).toFixed(2)}</div>
                    </div>
                    <div style="grid-column:1/-1;background:rgba(46,204,113,.08);border:1px solid rgba(46,204,113,.2);border-radius:10px;padding:16px;text-align:center;">
                        <div style="color:#8aa0c5;font-size:11px;text-transform:uppercase;margin-bottom:6px;">Total Revenue for ${date}</div>
                        <div style="color:#2ecc71;font-size:26px;font-weight:bold;">₱${parseFloat(d.total).toFixed(2)}</div>
                    </div>
                </div>`;
        }).catch(()=>{
            document.getElementById('customResult').innerHTML='<div style="color:#ff4d4d;text-align:center;padding:20px;">Error loading data.</div>';
        });
}
</script>
</body>
</html>

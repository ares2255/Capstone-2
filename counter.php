<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['admin_username']) && !isset($_SESSION['username'])) {
    header("Location: index.php"); exit();
}

$is_admin     = isset($_SESSION['admin_username']);
$display_user = $is_admin ? $_SESSION['admin_username'] : $_SESSION['username'];
$current_page = 'counter';

$pcs   = $pdo->query("SELECT * FROM pcs ORDER BY name ASC")->fetchAll();
$r     = $pdo->query("SELECT * FROM settings WHERE id=1")->fetch();
$today = date('Y-m-d');

$rev        = $pdo->prepare("SELECT COALESCE(SUM(cost),0) FROM sessions WHERE DATE(start_time)=:d"); $rev->execute([':d'=>$today]); $rev = $rev->fetchColumn();
$sess_count = $pdo->prepare("SELECT COUNT(*) FROM sessions WHERE DATE(start_time)=:d"); $sess_count->execute([':d'=>$today]); $sess_count = $sess_count->fetchColumn();
$print_count= $pdo->prepare("SELECT COUNT(*) FROM print_jobs WHERE DATE(created_at)=:d"); $print_count->execute([':d'=>$today]); $print_count = $print_count->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>The Desktop | Counter</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="includes/navbar.css">
<style>
html{overflow-y:scroll;scrollbar-gutter:stable;}
body{background-color:#050b14;background-image:linear-gradient(rgba(19,39,66,.3) 1px,transparent 1px),linear-gradient(90deg,rgba(19,39,66,.3) 1px,transparent 1px);background-size:50px 50px;color:white;font-family:'Segoe UI',sans-serif;margin:0;min-height:100vh;}
.page-wrap{max-width:1400px;margin:0 auto;padding:32px 40px;}
.stat-bar{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:28px;}
.stat-box{background:rgba(10,25,47,.85);border:1px solid #132742;border-radius:12px;padding:20px 24px;display:flex;align-items:center;gap:16px;}
.stat-icon{width:44px;height:44px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;}
.si-rev{background:#0c3b4a;color:#00e0ff;}.si-sess{background:#0f3f2e;color:#19ff9c;}.si-print{background:#3a1a5c;color:#c084ff;}
.stat-info h3{margin:0;font-size:22px;}.stat-info p{margin:2px 0 0;color:#8aa0c5;font-size:11px;text-transform:uppercase;letter-spacing:1px;}
.section-title{font-size:15px;color:#38bdf8;margin-bottom:16px;display:flex;align-items:center;gap:8px;}
.pc-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(170px,1fr));gap:16px;}
.pc-card{background:rgba(10,25,47,.85);border:1px solid #1a3352;border-radius:12px;padding:20px 16px;text-align:center;transition:transform .2s,border-color .2s,box-shadow .2s;}
.pc-card:hover{transform:translateY(-3px);border-color:#38bdf8;box-shadow:0 8px 24px rgba(56,189,248,.1);}
.pc-card.in-use{border-color:#ff8c00;}.pc-card.in-use:hover{border-color:#ffae00;}
.pc-icon{font-size:32px;margin-bottom:10px;color:#38bdf8;}.pc-card.in-use .pc-icon{color:#ffae00;}
.pc-name{font-size:14px;font-weight:700;margin-bottom:8px;}
.pc-status{display:inline-block;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;margin-bottom:12px;}
.status-available{background:#0f3f2e;color:#19ff9c;}.status-active{background:#4a2b06;color:#ffae00;}
.pc-timer{font-family:monospace;font-size:12px;color:#8aa0c5;margin-bottom:12px;min-height:16px;}
.btn-start{display:block;width:100%;padding:8px 0;background:#38bdf8;color:#000;border:none;border-radius:7px;font-weight:700;font-size:13px;cursor:pointer;transition:background .2s;}
.btn-start:hover{background:#22d3ee;}
.btn-end{display:block;width:100%;padding:8px 0;background:#ff4d4d;color:#fff;border:none;border-radius:7px;font-weight:700;font-size:13px;cursor:pointer;transition:background .2s;}
.btn-end:hover{background:#e03030;}
.modal-overlay{display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.85);backdrop-filter:blur(6px);z-index:9999;align-items:center;justify-content:center;}
.modal-box{background:#0a192f;border:1px solid #38bdf8;border-radius:14px;padding:30px;width:360px;text-align:center;}
.modal-box h3{margin:0 0 6px;font-size:18px;}.modal-box p{color:#8aa0c5;font-size:13px;margin-bottom:20px;}
.time-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:20px;}
.time-btn{padding:12px;background:#0f1c2e;border:1px solid #1a3352;border-radius:8px;color:white;cursor:pointer;font-size:13px;font-weight:600;transition:.2s;}
.time-btn:hover,.time-btn.selected{border-color:#38bdf8;background:rgba(56,189,248,.15);color:#38bdf8;}
.modal-actions{display:flex;gap:10px;}
.btn-cancel{flex:1;background:#1e293b;color:#94a3b8;border:none;padding:11px;border-radius:8px;cursor:pointer;}
.btn-confirm{flex:1;background:#38bdf8;color:#000;border:none;padding:11px;border-radius:8px;cursor:pointer;font-weight:700;}
.end-modal-box{background:#0a192f;border:1px solid #ff4d4d;border-radius:14px;padding:30px;width:360px;text-align:center;box-shadow:0 0 24px rgba(255,77,77,.2);}
.end-modal-box i{color:#ff4d4d;font-size:44px;display:block;margin-bottom:14px;}
.end-modal-box h3{margin:0 0 6px;}.end-modal-box p{color:#8aa0c5;font-size:14px;margin-bottom:24px;}
.btn-end-cancel{flex:1;background:#1e293b;color:white;border:none;padding:12px;border-radius:8px;cursor:pointer;font-weight:bold;}
.btn-end-confirm{flex:1;background:#ff4d4d;color:white;border:none;padding:12px;border-radius:8px;cursor:pointer;font-weight:bold;}
.toast{position:fixed;top:80px;right:24px;padding:14px 20px;border-radius:10px;font-size:14px;z-index:3000;animation:toastIn .4s ease-out;display:flex;align-items:center;gap:10px;}
.toast.success{background:#2ecc71;color:white;}.toast.info{background:#38bdf8;color:#000;}
@keyframes toastIn{from{transform:translateX(110%);opacity:0}to{transform:translateX(0);opacity:1}}
</style>
</head>
<body>
<?php include 'includes/navbar.php'; ?>
<div class="page-wrap">
    <div class="stat-bar">
        <div class="stat-box"><div class="stat-icon si-rev"><i class="fas fa-peso-sign"></i></div><div class="stat-info"><h3 style="color:#00e0ff;">₱<?= number_format($rev,2) ?></h3><p>Today's Revenue</p></div></div>
        <div class="stat-box"><div class="stat-icon si-sess"><i class="fas fa-desktop"></i></div><div class="stat-info"><h3 style="color:#19ff9c;"><?= $sess_count ?></h3><p>Sessions Today</p></div></div>
        <div class="stat-box"><div class="stat-icon si-print"><i class="fas fa-print"></i></div><div class="stat-info"><h3 style="color:#c084ff;"><?= $print_count ?></h3><p>Print Jobs Today</p></div></div>
    </div>

    <div class="section-title"><i class="fas fa-th"></i> PC Units</div>
    <div class="pc-grid">
        <?php foreach($pcs as $pc):
            $isActive  = $pc['status'] === 'active';
            $startTime = '';
            if ($isActive) {
                $sq = $pdo->prepare("SELECT start_time FROM sessions WHERE pc_id=:id AND end_time IS NULL ORDER BY id DESC LIMIT 1");
                $sq->execute([':id' => $pc['id']]);
                $sr = $sq->fetch();
                $startTime = $sr['start_time'] ?? '';
            }
        ?>
        <div class="pc-card <?= $isActive ? 'in-use' : '' ?>">
            <div class="pc-icon"><i class="fas fa-desktop"></i></div>
            <div class="pc-name"><?= htmlspecialchars($pc['name']) ?></div>
            <span class="pc-status <?= $isActive ? 'status-active' : 'status-available' ?>"><?= $isActive ? 'In Use' : 'Available' ?></span>
            <?php if($isActive && $startTime): ?>
            <div class="pc-timer" id="timer-<?= $pc['id'] ?>" data-start="<?= $startTime ?>">--:--</div>
            <?php else: ?><div class="pc-timer"></div><?php endif; ?>
            <?php if($isActive): ?>
                <button class="btn-end" onclick="openEndModal(<?= $pc['id'] ?>,'<?= htmlspecialchars($pc['name']) ?>')"><i class="fas fa-stop-circle"></i> End Session</button>
            <?php else: ?>
                <button class="btn-start" onclick="openStartModal(<?= $pc['id'] ?>,'<?= htmlspecialchars($pc['name']) ?>')"><i class="fas fa-play-circle"></i> Start</button>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<div id="startModal" class="modal-overlay">
    <div class="modal-box">
        <h3 id="startModalTitle">Start Session</h3>
        <p>Choose a time package for this PC</p>
        <div class="time-grid">
            <button class="time-btn" onclick="selectTime(this,60)">1 Hour — ₱<?= $r['hourly_rate'] ?? '—' ?></button>
            <button class="time-btn" onclick="selectTime(this,180)">3 Hours — ₱<?= $r['rate_3hr'] ?? '—' ?></button>
            <button class="time-btn" onclick="selectTime(this,300)">5 Hours — ₱<?= $r['rate_5hr'] ?? '—' ?></button>
            <button class="time-btn" onclick="selectTime(this,420)">7 Hours — ₱<?= $r['rate_7hr'] ?? '—' ?></button>
            <button class="time-btn" onclick="selectTime(this,720)">12 Hours — ₱<?= $r['rate_12hr'] ?? '—' ?></button>
            <button class="time-btn" onclick="selectTime(this,0)">Open-Ended</button>
        </div>
        <div class="modal-actions">
            <button class="btn-cancel" onclick="closeStartModal()">Cancel</button>
            <button class="btn-confirm" onclick="confirmStart()">Start Session</button>
        </div>
    </div>
</div>

<div id="endModal" class="modal-overlay">
    <div class="end-modal-box">
        <i class="fas fa-stop-circle"></i>
        <h3 id="endModalTitle">End Session?</h3>
        <p>This will stop the session and calculate the final cost.</p>
        <div class="modal-actions">
            <button class="btn-end-cancel" onclick="closeEndModal()">Cancel</button>
            <button class="btn-end-confirm" id="confirmEndBtn">Yes, End It</button>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('[id^="timer-"]').forEach(el=>{
    const start=new Date(el.dataset.start.replace(' ','T'));
    function tick(){const d=Math.floor((Date.now()-start)/1000);el.textContent=String(Math.floor(d/3600)).padStart(2,'0')+':'+String(Math.floor((d%3600)/60)).padStart(2,'0')+':'+String(d%60).padStart(2,'0');}
    tick();setInterval(tick,1000);
});
let currentPcId=null,selectedMins=null;
function openStartModal(id,name){currentPcId=id;selectedMins=null;document.getElementById('startModalTitle').textContent='Start: '+name;document.querySelectorAll('.time-btn').forEach(b=>b.classList.remove('selected'));document.getElementById('startModal').style.display='flex';}
function closeStartModal(){document.getElementById('startModal').style.display='none';}
function selectTime(btn,mins){selectedMins=mins;document.querySelectorAll('.time-btn').forEach(b=>b.classList.remove('selected'));btn.classList.add('selected');}
function confirmStart(){if(selectedMins===null){alert('Please select a time package.');return;}window.location.href='start_session.php?id='+currentPcId+'&mins='+selectedMins;}
function openEndModal(id,name){currentPcId=id;document.getElementById('endModalTitle').textContent='End session for '+name+'?';document.getElementById('endModal').style.display='flex';document.getElementById('confirmEndBtn').onclick=()=>{window.location.href='end_session.php?id='+currentPcId;};}
function closeEndModal(){document.getElementById('endModal').style.display='none';}
['startModal','endModal'].forEach(id=>{document.getElementById(id).addEventListener('click',e=>{if(e.target.id===id)document.getElementById(id).style.display='none';});});
const p=new URLSearchParams(location.search);
if(p.get('status')==='started'){showToast('Session started!','info');history.replaceState({},'',location.pathname);}
if(p.get('status')==='ended'){showToast('Session ended — '+p.get('pc')+' · ₱'+parseFloat(p.get('paid')).toFixed(2),'success');history.replaceState({},'',location.pathname);}
function showToast(msg,type){const t=document.createElement('div');t.className='toast '+type;t.innerHTML='<i class="fas fa-check-circle"></i> '+msg;document.body.appendChild(t);setTimeout(()=>t.remove(),4000);}
setTimeout(()=>location.reload(),30000);
</script>
</body></html>

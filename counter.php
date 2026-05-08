<?php
session_start();
error_reporting(0);
ini_set('display_errors', 0);
include "config/db.php";

if (!isset($_SESSION['admin_username']) && !isset($_SESSION['username'])) {
    header("Location: index.php"); exit();
}

$is_admin     = isset($_SESSION['admin_username']);
$display_user = $is_admin ? $_SESSION['admin_username'] : $_SESSION['username'];

$pcs   = $pdo->query("SELECT * FROM pcs ORDER BY name ASC")->fetchAll();
$r     = $pdo->query("SELECT * FROM settings WHERE id=1")->fetch();
$packages = $pdo->query("SELECT * FROM packages ORDER BY minutes ASC")->fetchAll();
$today = date('Y-m-d');

$rev        = $pdo->prepare("SELECT COALESCE(SUM(cost),0) FROM sessions WHERE DATE(start_time)=:d"); $rev->execute([':d'=>$today]); $rev = $rev->fetchColumn();
$sess_count = $pdo->prepare("SELECT COUNT(*) FROM sessions WHERE DATE(start_time)=:d"); $sess_count->execute([':d'=>$today]); $sess_count = $sess_count->fetchColumn();
$active_count = $pdo->query("SELECT COUNT(*) FROM pcs WHERE status='active'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>The Desktop | Counter</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="includes/navbar.css">
<style>
*{box-sizing:border-box;}
html{overflow-y:scroll;scrollbar-gutter:stable;}
body{background:#0a0f1a;color:white;font-family:'Segoe UI',sans-serif;margin:0;min-height:100vh;
background-image:linear-gradient(rgba(19,39,66,.25) 1px,transparent 1px),linear-gradient(90deg,rgba(19,39,66,.25) 1px,transparent 1px);background-size:40px 40px;}

.page-wrap{max-width:1500px;margin:0 auto;padding:28px 36px;}

/* Header */
.page-header{margin-bottom:24px;}
.page-header h2{margin:0 0 4px;font-size:20px;font-weight:700;}
.page-header p{margin:0;color:#4a5f7a;font-size:13px;}

/* Stat Bar */
.stat-bar{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:28px;}
.stat-box{background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.07);border-radius:12px;padding:18px 22px;display:flex;align-items:center;gap:14px;}
.stat-icon{width:42px;height:42px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;}
.si-rev{background:rgba(0,224,255,.1);color:#00e0ff;}
.si-sess{background:rgba(25,255,156,.1);color:#19ff9c;}
.si-active{background:rgba(255,165,0,.1);color:#ffa500;}
.stat-info h3{margin:0;font-size:20px;font-weight:700;}
.stat-info p{margin:2px 0 0;color:#4a5f7a;font-size:11px;text-transform:uppercase;letter-spacing:1px;}

/* PC Grid */
.pc-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:16px;}

.pc-card{
    background:rgba(255,255,255,.03);
    border:1px solid rgba(255,255,255,.08);
    border-radius:14px;padding:22px 18px;
    text-align:center;cursor:pointer;
    transition:all .25s ease;
    position:relative;overflow:hidden;
}
.pc-card::before{content:'';position:absolute;top:0;left:0;right:0;height:2px;background:transparent;transition:.3s;}
.pc-card:hover{background:rgba(255,255,255,.06);border-color:rgba(255,255,255,.15);transform:translateY(-2px);}

/* Available */
.pc-card.available::before{background:#19ff9c;}
.pc-card.available:hover{border-color:#19ff9c;box-shadow:0 8px 32px rgba(25,255,156,.1);}

/* Active/In Use */
.pc-card.in-use{border-color:rgba(255,140,0,.4);}
.pc-card.in-use::before{background:#ffa500;}
.pc-card.in-use:hover{border-color:#ffa500;box-shadow:0 8px 32px rgba(255,165,0,.15);}

/* Overtime */
.pc-card.overtime{
    border-color:#ff2020 !important;
    animation:overtimePulse 1.2s ease-in-out infinite;
}
.pc-card.overtime::before{background:#ff2020;}
@keyframes overtimePulse{
    0%,100%{box-shadow:0 0 16px rgba(255,32,32,.4);}
    50%{box-shadow:0 0 36px rgba(255,32,32,.8),0 0 60px rgba(255,32,32,.3);}
}

/* PC Icon */
.pc-icon{font-size:28px;margin-bottom:10px;transition:.3s;}
.pc-card.available .pc-icon{color:#4a5f7a;}
.pc-card.in-use .pc-icon{color:#ffa500;}
.pc-card.overtime .pc-icon{color:#ff2020;animation:blink .8s infinite;}

/* PC Name */
.pc-name{font-size:16px;font-weight:700;margin-bottom:8px;letter-spacing:.5px;}
.pc-card.overtime .pc-name{color:#ff2020;}

/* Status dot */
.status-dot{display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:1px;margin-bottom:12px;}
.dot{width:7px;height:7px;border-radius:50%;display:inline-block;}
.dot-avail{background:#19ff9c;box-shadow:0 0 6px #19ff9c;}
.dot-active{background:#ffa500;box-shadow:0 0 6px #ffa500;}
.dot-over{background:#ff2020;box-shadow:0 0 6px #ff2020;animation:blink .8s infinite;}
.text-avail{color:#19ff9c;}
.text-active{color:#ffa500;}
.text-over{color:#ff2020;}

@keyframes blink{0%,100%{opacity:1;}50%{opacity:.2;}}

/* Timer */
.pc-timer{font-family:'Courier New',monospace;font-size:22px;font-weight:700;margin:8px 0;letter-spacing:1px;min-height:28px;}
.timer-avail{color:#4a5f7a;font-size:12px;}
.timer-running{color:#2ecc71;}
.timer-warning{color:#f1c40f;}
.timer-critical{color:#ff6b35;}
.timer-over{color:#ff2020;animation:blink .8s infinite;}

/* Cost display */
.cost-display{font-size:13px;color:#ffa500;margin-bottom:10px;font-weight:600;}

/* Action hint */
.action-hint{font-size:11px;color:#4a5f7a;margin-top:8px;}

/* Overtime badge */
.overtime-badge{
    display:none;background:#ff2020;color:white;
    font-size:10px;font-weight:700;padding:3px 10px;
    border-radius:20px;text-transform:uppercase;letter-spacing:1px;
    margin-bottom:8px;animation:blink .8s infinite;
}
.overtime-badge.show{display:inline-block;}

/* Alarm Bar */
.alarm-bar{
    display:none;position:fixed;top:60px;left:0;right:0;
    background:linear-gradient(90deg,#8b0000,#ff0000,#8b0000);
    color:white;padding:10px 20px;text-align:center;
    font-weight:700;font-size:14px;z-index:500;
    letter-spacing:1px;animation:blink .7s infinite;
    border-bottom:2px solid #ff4d4d;
}
.alarm-bar.show{display:block;}

/* Modal */
.modal-overlay{display:none;position:fixed;top:0;left:0;width:100%;height:100%;
background:rgba(0,0,0,.8);backdrop-filter:blur(8px);z-index:9999;
align-items:center;justify-content:center;overflow-y:auto;padding:20px 0;}
.modal-overlay.show{display:flex;}

.modal-box{background:#0d1b2e;border:1px solid rgba(255,255,255,.1);border-radius:16px;padding:32px;width:420px;max-width:95vw;text-align:center;max-height:90vh;overflow-y:auto;margin:auto;}
.modal-title{font-size:20px;font-weight:700;margin-bottom:6px;color:#38bdf8;}
.modal-sub{color:#4a5f7a;font-size:13px;margin-bottom:24px;}

/* Open time button */
.btn-open-time{
    display:block;width:100%;padding:14px;
    background:#e74c3c;color:white;border:none;
    border-radius:10px;font-size:15px;font-weight:700;
    cursor:pointer;margin-bottom:12px;transition:.2s;
    letter-spacing:.5px;
}
.btn-open-time:hover{background:#c0392b;}

/* Package grid */
.pkg-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:20px;}
.pkg-btn{
    padding:13px 10px;background:transparent;
    border:1px solid rgba(56,189,248,.3);
    border-radius:10px;color:#38bdf8;
    cursor:pointer;font-size:13px;font-weight:600;
    transition:.2s;
}
.pkg-btn:hover,.pkg-btn.selected{
    background:rgba(56,189,248,.1);
    border-color:#38bdf8;color:white;
}
.pkg-btn.selected{border-color:#2ecc71;color:#2ecc71;background:rgba(46,204,113,.1);}

.btn-cancel-link{display:block;color:#4a5f7a;font-size:13px;cursor:pointer;margin-top:8px;text-decoration:underline;}
.btn-cancel-link:hover{color:#8aa0c5;}

/* End Modal */
.end-modal-box{background:#0d1b2e;border:1px solid rgba(255,77,77,.3);border-radius:16px;padding:32px;width:380px;max-width:95vw;text-align:center;box-shadow:0 0 40px rgba(255,0,0,.15);}
.end-modal-box .end-icon{font-size:48px;color:#ff4d4d;display:block;margin-bottom:16px;}
.end-modal-box h3{margin:0 0 8px;font-size:18px;}
.end-modal-box p{color:#4a5f7a;font-size:13px;margin-bottom:24px;}
.modal-actions{display:flex;gap:10px;}
.btn-stay{flex:1;background:rgba(255,255,255,.05);color:#8aa0c5;border:1px solid rgba(255,255,255,.1);padding:12px;border-radius:10px;cursor:pointer;font-size:14px;}
.btn-end-confirm{flex:1;background:#ff4d4d;color:white;border:none;padding:12px;border-radius:10px;cursor:pointer;font-weight:700;font-size:14px;}

/* Toast */
.toast{position:fixed;bottom:28px;right:24px;padding:14px 20px;border-radius:10px;font-size:14px;z-index:5000;display:flex;align-items:center;gap:10px;box-shadow:0 8px 24px rgba(0,0,0,.4);}
.toast.success{background:#2ecc71;color:white;}
.toast.info{background:#38bdf8;color:#000;}
@keyframes toastIn{from{transform:translateY(20px);opacity:0}to{transform:translateY(0);opacity:1}}
</style>
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div class="alarm-bar" id="alarmBar">
    <i class="fas fa-exclamation-triangle"></i> &nbsp;
    OVERTIME ALERT — PC(s) have exceeded their time limit! Please attend to them.
    &nbsp; <i class="fas fa-exclamation-triangle"></i>
</div>

<div class="page-wrap">
    <div class="page-header">
        <h2>PC Units Management</h2>
        <p>Monitor and manage active workstation sessions</p>
    </div>

    <div class="stat-bar">
        <div class="stat-box">
            <div class="stat-icon si-rev"><i class="fas fa-peso-sign"></i></div>
            <div class="stat-info"><h3 style="color:#00e0ff;">₱<?= number_format($rev,2) ?></h3><p>Today's Revenue</p></div>
        </div>
        <div class="stat-box">
            <div class="stat-icon si-sess"><i class="fas fa-desktop"></i></div>
            <div class="stat-info"><h3 style="color:#19ff9c;"><?= $sess_count ?></h3><p>Sessions Today</p></div>
        </div>
        <div class="stat-box">
            <div class="stat-icon si-active"><i class="fas fa-circle-dot"></i></div>
            <div class="stat-info"><h3 style="color:#ffa500;"><?= $active_count ?></h3><p>Currently Active</p></div>
        </div>
    </div>

    <div class="pc-grid">
    <?php foreach($pcs as $pc):
        $isActive = $pc['status'] === 'active';
        $startTime = ''; $timeLimit = null;
        if ($isActive) {
            $sq = $pdo->prepare("SELECT start_time, time_limit FROM sessions WHERE pc_id=:id AND end_time IS NULL ORDER BY id DESC LIMIT 1");
            $sq->execute([':id' => $pc['id']]);
            $sr = $sq->fetch();
            $startTime = $sr['start_time'] ?? '';
            $timeLimit = $sr['time_limit'] ?? null;
        }

        // Pre-calculate cost for display using packages table
        $cost = 0;
        if ($isActive && $timeLimit) {
            foreach($packages as $pkg) {
                if((int)$pkg['minutes'] === (int)$timeLimit) { $cost = $pkg['price']; break; }
            }
        }
    ?>
        <div class="pc-card <?= $isActive ? 'in-use' : 'available' ?>" id="pc-card-<?= $pc['id'] ?>"
             data-pc-id="<?= $pc['id'] ?>"
             data-pc-name="<?= htmlspecialchars($pc['name'], ENT_QUOTES) ?>"
             data-action="<?= $isActive ? 'end' : 'start' ?>">

            <div class="pc-icon"><i class="fas fa-desktop"></i></div>
            <div class="pc-name"><?= htmlspecialchars($pc['name']) ?></div>

            <?php if($isActive): ?>
                <div class="status-dot"><span class="dot dot-active"></span><span class="text-active">ACTIVE</span></div>
                <div class="overtime-badge" id="overtime-badge-<?= $pc['id'] ?>">⚠ OVERTIME</div>
                <div class="pc-timer timer-running" id="timer-<?= $pc['id'] ?>"
                     data-start="<?= $startTime ?>" data-limit="<?= $timeLimit ?>">--:--:--</div>
                <?php if($cost > 0): ?>
                <div class="cost-display">₱<?= number_format($cost,2) ?></div>
                <?php endif; ?>
                <div class="action-hint"><i class="fas fa-hand-pointer"></i> Click to end session</div>
            <?php else: ?>
                <div class="status-dot"><span class="dot dot-avail"></span><span class="text-avail">AVAILABLE</span></div>
                <div class="pc-timer timer-avail">—</div>
                <div class="action-hint"><i class="fas fa-hand-pointer"></i> Click to start</div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
    </div>
</div>

<!-- Start Modal -->
<div id="startModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-title" id="startModalTitle">Start Session</div>
        <div class="modal-sub">Select a time package or choose Open Time.</div>

        <button class="btn-open-time" onclick="selectPkg(this,0)">OPEN TIME</button>

        <div class="pkg-grid">
            <?php foreach($packages as $pkg):
                $h = intdiv($pkg['minutes'], 60);
                $m = $pkg['minutes'] % 60;
                if($h > 0 && $m > 0)  $label = "{$h}HR {$m}MIN";
                elseif($h > 0)        $label = $h == 1 ? "1 HR" : "{$h} HRS";
                else                  $label = "{$m} MIN";
                $isLast = ($pkg === end($packages));
                $style = $isLast ? "grid-column:1/-1;border-color:rgba(46,204,113,.4);color:#2ecc71;" : "";
            ?>
            <button class="pkg-btn" style="<?= $style ?>" onclick="selectPkg(this,<?= $pkg['minutes'] ?>)">
                <?= htmlspecialchars($label) ?> (&#8369;<?= number_format($pkg['price'],2) ?>)
            </button>
            <?php endforeach; ?>
        </div>

        <span class="btn-cancel-link" onclick="closeStartModal()">Cancel</span>
    </div>
</div>

<!-- End Modal -->
<div id="endModal" class="modal-overlay">
    <div class="end-modal-box">
        <i class="fas fa-stop-circle end-icon"></i>
        <h3 id="endModalTitle">End Session?</h3>
        <p>This will stop the session and calculate the final cost.</p>

        <div class="modal-actions">
            <button class="btn-stay" onclick="closeEndModal()">Cancel</button>
            <button class="btn-end-confirm" id="confirmEndBtn">End Session</button>
        </div>
    </div>
</div>

<script>
let alarmPlaying = false;
let anyOvertime = false;
let currentPcId = null;
let currentPcName = null;
let selectedMins = null;

// ── PC card clicks (event delegation – works even if inline onclick fails) ──
document.addEventListener('click', function(e) {
    const card = e.target.closest('.pc-card');
    if (!card) return;
    const id     = card.dataset.pcId;
    const name   = card.dataset.pcName;
    const action = card.dataset.action;
    if (action === 'start') openStartModal(id, name);
    else if (action === 'end') openEndModal(id, name);
});

// ── Timers ──
document.querySelectorAll('[id^="timer-"]').forEach(el => {
    const raw = el.dataset.start;
    if (!raw) return;
    const start = new Date(raw.replace(' ','T'));
    const limitMins = el.dataset.limit ? parseInt(el.dataset.limit) : null;
    const pcId = el.id.replace('timer-','');
    const card = document.getElementById('pc-card-' + pcId);
    const badge = document.getElementById('overtime-badge-' + pcId);
    const statusDot = card ? card.querySelector('.status-dot') : null;
    const pad = n => String(n).padStart(2,'0');

    function tick() {
        const elapsed = Math.floor((Date.now() - start) / 1000);
        if (limitMins && elapsed >= limitMins * 60) {
            const over = elapsed - (limitMins * 60);
            el.textContent = '+' + pad(Math.floor(over/3600)) + ':' + pad(Math.floor((over%3600)/60)) + ':' + pad(over%60);
            el.className = 'pc-timer timer-over';
            if (card) { card.classList.add('overtime'); card.classList.remove('in-use'); }
            if (badge) badge.classList.add('show');
            if (statusDot) statusDot.innerHTML = '<span class="dot dot-over"></span><span class="text-over">OVERTIME</span>';
            anyOvertime = true;
        } else if (limitMins) {
            const rem = (limitMins * 60) - elapsed;
            el.textContent = pad(Math.floor(rem/3600)) + ':' + pad(Math.floor((rem%3600)/60)) + ':' + pad(rem%60);
            const pct = rem / (limitMins * 60);
            el.className = pct <= 0.1 ? 'pc-timer timer-critical' : pct <= 0.25 ? 'pc-timer timer-warning' : 'pc-timer timer-running';
        } else {
            el.textContent = pad(Math.floor(elapsed/3600)) + ':' + pad(Math.floor((elapsed%3600)/60)) + ':' + pad(elapsed%60);
        }
    }
    tick(); setInterval(tick, 1000);
});

// ── Alarm ──
setInterval(() => {
    if (anyOvertime) {
        document.getElementById('alarmBar').classList.add('show');
        if (!alarmPlaying) {
            alarmPlaying = true;
            function beep() {
                if (!alarmPlaying) return;
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const o = ctx.createOscillator(), g = ctx.createGain();
                o.connect(g); g.connect(ctx.destination);
                o.type = 'square'; o.frequency.value = 900;
                g.gain.setValueAtTime(0.25, ctx.currentTime);
                g.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.3);
                o.start(); o.stop(ctx.currentTime + 0.3);
                setTimeout(beep, 2500);
            }
            beep();
        }
    }
}, 1000);

// ── Modal functions ──
function openStartModal(id, name) {
    currentPcId = id; currentPcName = name; selectedMins = null;
    document.getElementById('startModalTitle').textContent = 'Start ' + name;
    document.querySelectorAll('.pkg-btn,.btn-open-time').forEach(b => b.classList.remove('selected'));
    document.getElementById('startModal').classList.add('show');
}
function closeStartModal() { document.getElementById('startModal').classList.remove('show'); }

function selectPkg(btn, mins) {
    selectedMins = mins;
    document.querySelectorAll('.pkg-btn,.btn-open-time').forEach(b => b.classList.remove('selected'));
    btn.classList.add('selected');
    setTimeout(() => {
        window.location.href = 'start_session.php?id=' + currentPcId + '&mins=' + selectedMins;
    }, 200);
}

function openEndModal(id, name) {
    currentPcId = id; currentPcName = name;
    document.getElementById('endModalTitle').textContent = 'End session for ' + name + '?';
    document.getElementById('confirmEndBtn').onclick = () => { window.location.href = 'end_session.php?id=' + id; };
    document.getElementById('endModal').classList.add('show');
}
function closeEndModal() { document.getElementById('endModal').classList.remove('show'); }

function copyUrl() {
    const url = window.location.origin + '/session_display.php?pc=' + encodeURIComponent(currentPcName);
    navigator.clipboard.writeText(url).then(() => showToast('Session URL copied!', 'info'));
}

// Close modals on backdrop click
['startModal','endModal'].forEach(id => {
    document.getElementById(id).addEventListener('click', e => {
        if (e.target.id === id) document.getElementById(id).classList.remove('show');
    });
});

// Toast notifications
const p = new URLSearchParams(location.search);
if (p.get('status') === 'started') { showToast('Session started!', 'info'); history.replaceState({},'',location.pathname); }
if (p.get('status') === 'ended') { showToast('Session ended — ' + p.get('pc') + ' · ₱' + parseFloat(p.get('paid')||0).toFixed(2), 'success'); history.replaceState({},'',location.pathname); }

function showToast(msg, type) {
    const t = document.createElement('div');
    t.className = 'toast ' + type;
    t.style.animation = 'toastIn .3s ease-out';
    t.innerHTML = '<i class="fas fa-check-circle"></i> ' + msg;
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 4000);
}

setTimeout(() => location.reload(), 30000);
</script>
</body>
</html>

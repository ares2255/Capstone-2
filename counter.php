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
<title>Q-Solutions | Counter</title>
<link rel="icon" type="image/jpeg" href="q.jpg">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="includes/navbar.css">
<script>(function(){if(localStorage.getItem("settings_theme")==="light"){document.documentElement.classList.add("light-mode");}})()</script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
<style>
:root {
    --qs-navy: #1e2a78;
    --qs-blue: #2d3eaa;
    --qs-accent: #4a6cf7;
    --text-main: #1e293b;
    --text-muted: #64748b;
}

*{box-sizing:border-box;}
html{overflow-y:scroll;scrollbar-gutter:stable;}
body{
    background: linear-gradient(135deg, #0d1117 0%, #1a1a2e 50%, #16213e 100%);
    color:white;
    font-family:'Inter',sans-serif;
    margin:0;
    min-height:100vh;
}

.page-wrap{max-width:1500px;margin:0 auto;padding:28px 36px;}

/* Header */
.page-header{margin-bottom:24px;}
.page-header h2{margin:0 0 4px;font-size:22px;font-weight:700;color:white;letter-spacing:-0.3px;}
.page-header p{margin:0;color:#8aa0c5;font-size:13px;}

/* Stat Bar */
.stat-bar{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:28px;}
.stat-box{
    background:rgba(255,255,255,.05);
    border:1px solid rgba(255,255,255,.1);
    border-radius:16px;
    padding:18px 22px;
    display:flex;align-items:center;gap:14px;
    border-bottom: 4px solid var(--qs-navy);
    transition: transform .2s, box-shadow .2s;
}
.stat-box:hover{transform:translateY(-2px);box-shadow:0 8px 32px rgba(74,108,247,.15);}
.stat-icon{width:44px;height:44px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;}
.si-rev{background:rgba(0,224,255,.1);color:#00e0ff;}
.si-sess{background:rgba(25,255,156,.1);color:#19ff9c;}
.si-active{background:rgba(255,165,0,.1);color:#ffa500;}
.stat-info h3{margin:0;font-size:22px;font-weight:700;}
.stat-info p{margin:2px 0 0;color:#8aa0c5;font-size:11px;text-transform:uppercase;letter-spacing:1px;}

/* PC Grid */
.pc-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:16px;}

.pc-card{
    background:rgba(255,255,255,.04);
    border:1px solid rgba(255,255,255,.1);
    border-radius:18px;
    padding:24px 18px;
    text-align:center;cursor:pointer;
    transition:all .3s cubic-bezier(0.175,0.885,0.32,1.275);
    position:relative;overflow:hidden;
}
.pc-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:transparent;transition:.3s;}
.pc-card:hover{background:rgba(255,255,255,.07);transform:translateY(-4px);box-shadow:0 12px 40px rgba(74,108,247,.2);}

/* Available */
.pc-card.available::before{background:#19ff9c;}
.pc-card.available:hover{border-color:rgba(25,255,156,.4);box-shadow:0 12px 40px rgba(25,255,156,.12);}

/* Active/In Use */
.pc-card.in-use{border-color:rgba(255,140,0,.35);}
.pc-card.in-use::before{background:#ffa500;}
.pc-card.in-use:hover{border-color:rgba(255,165,0,.6);box-shadow:0 12px 40px rgba(255,165,0,.15);}

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
.pc-icon{font-size:30px;margin-bottom:12px;transition:.3s;}
.pc-card.available .pc-icon{color:#8aa0c5;}
.pc-card.in-use .pc-icon{color:#ffa500;}
.pc-card.overtime .pc-icon{color:#ff2020;animation:blink .8s infinite;}

/* PC Name */
.pc-name{font-size:16px;font-weight:700;margin-bottom:8px;letter-spacing:.5px;color:white;}
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
.timer-avail{color:#8aa0c5;font-size:12px;}
.timer-running{color:#2ecc71;}
.timer-warning{color:#f1c40f;}
.timer-critical{color:#ff6b35;}
.timer-over{color:#ff2020;animation:blink .8s infinite;}

/* Cost display */
.cost-display{font-size:13px;color:#ffa500;margin-bottom:10px;font-weight:600;}

/* Action hint */
.action-hint{font-size:11px;color:#8aa0c5;margin-top:8px;}

/* Overtime badge */
.overtime-badge{
    display:none;background:#ff2020;color:white;
    font-size:10px;font-weight:700;padding:3px 10px;
    border-radius:20px;text-transform:uppercase;letter-spacing:1px;
    margin-bottom:8px;animation:blink .8s infinite;
}
.overtime-badge.show{display:inline-block;}

/* Alarm Bar */
/* alarm-bar moved to navbar.php */

/* Modal */
.modal-overlay{
    display:none;position:fixed;top:0;left:0;width:100%;height:100%;
    background:rgba(0,0,0,.75);backdrop-filter:blur(10px);
    z-index:9999;align-items:center;justify-content:center;
    overflow-y:auto;padding:20px 0;
}
.modal-overlay.show{display:flex;}

.modal-box{
    background:#ffffff;
    border-radius:24px;
    padding:36px 32px;
    width:440px;max-width:95vw;
    text-align:center;
    max-height:90vh;overflow-y:auto;
    margin:auto;
    border-bottom:8px solid var(--qs-navy);
    box-shadow:0 20px 60px rgba(0,0,0,.5);
    color: #1e293b;
}
.modal-title{font-size:20px;font-weight:700;margin-bottom:6px;color:var(--qs-navy);}
.modal-sub{color:#64748b;font-size:13px;margin-bottom:24px;}

/* Open time button */
.btn-open-time{
    display:block;width:100%;padding:14px;
    background:var(--qs-navy);color:white;border:none;
    border-radius:10px;font-size:15px;font-weight:700;
    cursor:pointer;margin-bottom:12px;transition:.2s;
    letter-spacing:.5px;
}
.btn-open-time:hover{background:var(--qs-blue);transform:translateY(-1px);box-shadow:0 4px 16px rgba(30,42,120,.3);}

/* Package grid */
.pkg-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:20px;}
.pkg-btn{
    padding:13px 10px;
    background:transparent;
    border:1px solid rgba(30,42,120,.25);
    border-radius:10px;
    color:var(--qs-navy);
    cursor:pointer;font-size:13px;font-weight:600;
    transition:.2s;
}
.pkg-btn:hover,.pkg-btn.selected{
    background:rgba(30,42,120,.08);
    border-color:var(--qs-navy);
    color:var(--qs-navy);
}
.pkg-btn.selected{border-color:#2ecc71;color:#16a34a;background:rgba(46,204,113,.1);}

.btn-cancel-link{display:block;color:#64748b;font-size:13px;cursor:pointer;margin-top:8px;text-decoration:underline;}
.btn-cancel-link:hover{color:var(--qs-navy);}

/* End Modal */
.end-modal-box{
    background:#ffffff;
    border-radius:24px;
    padding:36px 32px;
    width:400px;max-width:95vw;
    text-align:center;
    border-bottom:8px solid var(--qs-navy);
    box-shadow:0 20px 60px rgba(0,0,0,.5);
    color:#1e293b;
}
.end-modal-box .end-icon{font-size:48px;color:var(--qs-blue);display:block;margin-bottom:16px;}
.end-modal-box h3{margin:0 0 8px;font-size:18px;color:#1e293b;}
.end-modal-box p{color:#64748b;font-size:13px;margin-bottom:24px;}
.modal-actions{display:flex;gap:10px;}
.btn-add-time-switch{flex:1;background:#1e2a78;color:white;border:none;padding:12px;border-radius:10px;cursor:pointer;font-weight:700;font-size:14px;transition:.2s;display:flex;align-items:center;justify-content:center;gap:6px;}
.btn-add-time-switch:hover{background:#2d3eaa;}
.btn-stay{flex:1;background:#f1f5f9;color:#64748b;border:1px solid #e2e8f0;padding:12px;border-radius:10px;cursor:pointer;font-size:14px;font-weight:600;transition:.2s;}
.btn-stay:hover{background:#e2e8f0;}
.btn-end-confirm{flex:1;background:var(--qs-navy);color:white;border:none;padding:12px;border-radius:10px;cursor:pointer;font-weight:700;font-size:14px;transition:.2s;}
.btn-end-confirm:hover{background:var(--qs-blue);}

/* Toast */
.toast{position:fixed;bottom:28px;right:24px;padding:14px 20px;border-radius:12px;font-size:14px;z-index:5000;display:flex;align-items:center;gap:10px;box-shadow:0 8px 24px rgba(0,0,0,.4);font-family:'Inter',sans-serif;font-weight:600;}
.toast.success{background:#2ecc71;color:white;}
.toast.info{background:#4a6cf7;color:white;}
@keyframes toastIn{from{transform:translateY(20px);opacity:0}to{transform:translateY(0);opacity:1}}
</style>
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<!-- alarm bar now in navbar.php -->

<div class="page-wrap">
    <div class="page-header">
        <h2>PC Units Management</h2>
        <p>Monitor and manage active workstation sessions</p>
    </div>

    <div class="stat-bar">
        <div class="stat-box">
            <div class="stat-icon si-rev"><i class="fas fa-peso-sign"></i></div>
            <div class="stat-info"><h3 id="stat-rev" style="color:#00e0ff;">₱<?= number_format($rev,2) ?></h3><p>Today's Revenue</p></div>
        </div>
        <div class="stat-box">
            <div class="stat-icon si-sess"><i class="fas fa-desktop"></i></div>
            <div class="stat-info"><h3 id="stat-sessions" style="color:#19ff9c;"><?= $sess_count ?></h3><p>Sessions Today</p></div>
        </div>
        <div class="stat-box">
            <div class="stat-icon si-active"><i class="fas fa-circle-dot"></i></div>
            <div class="stat-info"><h3 id="stat-active" style="color:#ffa500;"><?= $active_count ?></h3><p>Currently Active</p></div>
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

<!-- End / Overtime Modal -->
<div id="endModal" class="modal-overlay">
    <div class="end-modal-box">
        <!-- Default: End Session view -->
        <div id="endView">
            <i class="fas fa-stop-circle end-icon"></i>
            <h3 id="endModalTitle">End Session?</h3>
            <p id="endModalSub">This will stop the session and calculate the final cost.</p>
            <div class="modal-actions">
                <button class="btn-stay" onclick="closeEndModal()">Cancel</button>
                <button class="btn-add-time-switch" id="btnSwitchAddTime" onclick="showAddTimeView()" style="display:none">
                    <i class="fas fa-plus-circle"></i> Add Time
                </button>
                <button class="btn-end-confirm" id="confirmEndBtn">End Session</button>
            </div>
        </div>

        <!-- Add Time view -->
        <div id="addTimeView" style="display:none">
            <i class="fas fa-clock end-icon" style="color:#1e2a78"></i>
            <h3 id="addTimeTitle">Add Time</h3>
            <p>Select a package to extend the session.</p>
            <div class="pkg-grid" id="addTimePkgGrid">
                <?php foreach($packages as $pkg):
                    $h = intdiv($pkg['minutes'], 60);
                    $m = $pkg['minutes'] % 60;
                    if($h > 0 && $m > 0)  $label = "{$h}HR {$m}MIN";
                    elseif($h > 0)        $label = $h == 1 ? "1 HR" : "{$h} HRS";
                    else                  $label = "{$m} MIN";
                ?>
                <button class="pkg-btn" onclick="confirmAddTime(<?= $pkg['minutes'] ?>, this)">
                    <?= htmlspecialchars($label) ?> (&#8369;<?= number_format($pkg['price'],2) ?>)
                </button>
                <?php endforeach; ?>
            </div>
            <span class="btn-cancel-link" onclick="showEndView()" style="margin-top:12px;display:block">← Back</span>
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
        document.getElementById('globalAlarmBar').classList.add('show');
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

function _lockAllButtons() {
    document.querySelectorAll('button, .btn-cancel-link').forEach(b => {
        b.style.pointerEvents = 'none';
        b.style.opacity = '0.5';
    });
}

function selectPkg(btn, mins) {
    if (btn._locked) return;
    btn._locked = true;
    selectedMins = mins;
    document.querySelectorAll('.pkg-btn,.btn-open-time').forEach(b => b.classList.remove('selected'));
    btn.classList.add('selected');
    btn.style.opacity = '0.6';
    btn.textContent = 'Starting...';
    setTimeout(() => {
        _lockAllButtons();
        window.location.href = 'start_session.php?id=' + currentPcId + '&mins=' + selectedMins;
    }, 200);
}

function openEndModal(id, name) {
    currentPcId = id; currentPcName = name;
    const card = document.getElementById('pc-card-' + id);
    const isOvertime = card && card.classList.contains('overtime');

    document.getElementById('endModalTitle').textContent = 'End session for ' + name + '?';
    document.getElementById('endModalSub').textContent   = 'This will stop the session and calculate the final cost.';
    document.getElementById('confirmEndBtn').onclick = () => endSessionNow(id, name);

    // Show "Add Time" button only when PC is in overtime
    document.getElementById('btnSwitchAddTime').style.display = isOvertime ? 'flex' : 'none';

    showEndView();
    document.getElementById('endModal').classList.add('show');
}
function closeEndModal() {
    document.getElementById('endModal').classList.remove('show');
    showEndView();
}
function showAddTimeView() {
    document.getElementById('endView').style.display    = 'none';
    document.getElementById('addTimeView').style.display = 'block';
    document.getElementById('addTimeTitle').textContent  = 'Add Time — ' + currentPcName;
}
function showEndView() {
    document.getElementById('endView').style.display    = 'block';
    document.getElementById('addTimeView').style.display = 'none';
}
function confirmAddTime(mins, btn) {
    if (btn._locked) return;
    btn._locked = true;
    btn.disabled = true;
    btn.style.opacity = '0.6';
    btn.textContent = 'Adding...';
    _lockAllButtons();
    window.location.href = 'add_time.php?id=' + currentPcId + '&mins=' + mins;
}

function endSessionNow(id, name) {
    // Lock button to prevent double tap
    const confirmBtn = document.getElementById('confirmEndBtn');
    if (confirmBtn._locked) return;
    confirmBtn._locked = true;
    confirmBtn.disabled = true;
    confirmBtn.textContent = 'Ending...';
    confirmBtn.style.opacity = '0.6';

    // Close modal
    closeEndModal();

    // ── Immediately update the UI — no page reload ──
    const card  = document.getElementById('pc-card-' + id);
    const timer = document.getElementById('timer-' + id);
    const badge = document.getElementById('overtime-badge-' + id);

    // Reset card to available state right away
    if (card) {
        card.className = 'pc-card available';
        card.dataset.action = 'start';
        card.style.opacity = '';
        card.style.pointerEvents = '';
        card.innerHTML = `
            <div class="pc-icon"><i class="fas fa-desktop"></i></div>
            <div class="pc-name">${name}</div>
            <div class="status-dot"><span class="dot dot-avail"></span><span class="text-avail">AVAILABLE</span></div>
            <div class="pc-timer timer-avail">—</div>
            <div class="action-hint"><i class="fas fa-hand-pointer"></i> Click to start</div>
        `;
    }

    // Hide overtime bar immediately — no waiting for poll
    const bar      = document.getElementById('overtimeBar');
    const navBadge = document.getElementById('otNavBadge');
    const navCount = document.getElementById('otNavCount');
    if (bar)   { bar.classList.remove('show'); }
    if (navBadge) { navBadge.style.display = 'none'; }
    window._alarming = false;

    // Update stat counters
    const statActive = document.getElementById('stat-active');
    if (statActive) {
        const cur = parseInt(statActive.textContent) || 0;
        if (cur > 0) statActive.textContent = cur - 1;
    }

    showToast('Session ended for ' + name, 'info');

    // Call server in background — no redirect needed
    fetch('end_session.php?id=' + id)
        .then(() => {
            // Refresh stats after server confirms
            fetch('counter.php')
                .then(r => r.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    ['stat-rev','stat-sessions','stat-active'].forEach(sid => {
                        const el = document.getElementById(sid);
                        const newEl = doc.getElementById(sid);
                        if (el && newEl) el.textContent = newEl.textContent;
                    });
                });
        })
        .catch(() => {});
}

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

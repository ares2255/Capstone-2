<?php
// Customer-facing page - no login required
// URL: session_display.php?pc=PC-01
include "config/db.php";

$pc_name = $_GET['pc'] ?? '';
if (!$pc_name) { die("No PC specified. Use ?pc=PC-01"); }

try {
    $pc = $pdo->prepare("SELECT * FROM pcs WHERE name=:n");
    $pc->execute([':n' => $pc_name]);
    $pc = $pc->fetch();
    if (!$pc) die("PC not found.");

    $sess = null;
    if ($pc['status'] === 'active') {
        $q = $pdo->prepare("SELECT * FROM sessions WHERE pc_id=:id AND end_time IS NULL ORDER BY id DESC LIMIT 1");
        $q->execute([':id' => $pc['id']]);
        $sess = $q->fetch();
    }

    $rates = $pdo->query("SELECT * FROM settings WHERE id=1")->fetch();
} catch(PDOException $e) { die("DB Error: ".$e->getMessage()); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta http-equiv="refresh" content="30">
<title>TheDesktop — <?= htmlspecialchars($pc_name) ?></title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{background:linear-gradient(135deg,#0d1117 0%,#1a1a2e 50%,#16213e 100%);color:white;font-family:'Inter',sans-serif;min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;}

.top-bar{position:fixed;top:0;left:0;right:0;background:rgba(13,17,23,.95);border-bottom:1px solid rgba(255,255,255,.08);padding:12px 24px;display:flex;align-items:center;justify-content:space-between;z-index:100;backdrop-filter:blur(10px);}
.brand{font-size:18px;font-weight:700;}.brand strong{color:#7b9cff;}
.clock-bar{font-family:monospace;font-size:14px;color:#7b9cff;background:rgba(74,108,247,.1);border:1px solid rgba(74,108,247,.2);padding:6px 14px;border-radius:8px;}

/* ACTIVE SESSION */
.session-card{background:rgba(255,255,255,.05);border:2px solid #4a6cf7;border-radius:24px;padding:48px 56px;text-align:center;max-width:520px;width:90%;box-shadow:0 0 60px rgba(74,108,247,.2);backdrop-filter:blur(10px);}
.pc-label{font-size:14px;color:#8aa0c5;text-transform:uppercase;letter-spacing:2px;margin-bottom:8px;}
.pc-title{font-size:28px;font-weight:700;margin-bottom:32px;color:#7b9cff;}

.timer-ring{position:relative;width:220px;height:220px;margin:0 auto 32px;}
.timer-ring svg{transform:rotate(-90deg);}
.ring-bg{fill:none;stroke:rgba(255,255,255,.08);stroke-width:12;}
.ring-progress{fill:none;stroke:#4a6cf7;stroke-width:12;stroke-linecap:round;transition:stroke-dashoffset 1s linear,stroke .5s;}
.ring-expired{stroke:#ff4d4d;}
.ring-warning{stroke:#f1c40f;}
.timer-text{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);text-align:center;}
.time-remaining{font-family:monospace;font-size:38px;font-weight:700;line-height:1;}
.time-label{font-size:12px;color:#8aa0c5;text-transform:uppercase;letter-spacing:1px;margin-top:6px;}

.time-elapsed{font-size:13px;color:#8aa0c5;margin-bottom:8px;}
.package-badge{display:inline-block;background:rgba(74,108,247,.12);border:1px solid rgba(74,108,247,.3);color:#7b9cff;padding:6px 16px;border-radius:20px;font-size:13px;margin-bottom:28px;}

.end-btn{display:inline-flex;align-items:center;gap:8px;background:#ff4d4d;color:white;border:none;padding:14px 32px;border-radius:10px;font-size:15px;font-weight:700;cursor:pointer;transition:.2s;text-decoration:none;}
.end-btn:hover{background:#e03030;transform:translateY(-2px);}

.expired-card{background:rgba(10,25,47,.9);border:2px solid #ff4d4d;border-radius:20px;padding:48px 56px;text-align:center;max-width:520px;width:90%;box-shadow:0 0 60px rgba(255,77,77,.2);}
.expired-icon{font-size:64px;color:#ff4d4d;margin-bottom:16px;animation:pulse 2s infinite;}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.5}}
.expired-title{font-size:28px;font-weight:700;color:#ff4d4d;margin-bottom:8px;}
.expired-sub{color:#8aa0c5;font-size:14px;margin-bottom:28px;}
.call-staff{background:rgba(255,77,77,.1);border:1px solid rgba(255,77,77,.3);border-radius:12px;padding:20px;margin-top:16px;}
.call-staff p{color:#ff4d4d;font-size:16px;font-weight:600;}
.call-staff small{color:#8aa0c5;font-size:13px;}

/* AVAILABLE */
.idle-card{background:rgba(255,255,255,.04);border:2px solid rgba(255,255,255,.1);border-radius:24px;padding:48px 56px;text-align:center;max-width:520px;width:90%;}
.idle-icon{font-size:64px;color:#4a5f7a;margin-bottom:16px;}
.idle-title{font-size:24px;font-weight:700;color:#8aa0c5;margin-bottom:8px;}
.idle-sub{color:#4a5f7a;font-size:14px;}

/* Confirm Modal */
.modal-bg{display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.85);backdrop-filter:blur(8px);z-index:999;align-items:center;justify-content:center;}
.modal-bg.show{display:flex;}
.modal-box{background:#ffffff;border-radius:24px;padding:36px;max-width:400px;width:90%;text-align:center;border-bottom:8px solid #ff4d4d;box-shadow:0 20px 60px rgba(0,0,0,.5);color:#1e293b;}
.modal-box i{font-size:48px;color:#ff4d4d;margin-bottom:16px;display:block;}
.modal-box h3{font-size:20px;margin-bottom:8px;color:#1e293b;}
.modal-box p{color:#64748b;font-size:14px;margin-bottom:24px;}
.modal-actions{display:flex;gap:12px;}
.btn-cancel{flex:1;background:#f1f5f9;color:#64748b;border:1px solid #e2e8f0;padding:12px;border-radius:8px;cursor:pointer;font-size:14px;font-weight:600;transition:.2s;}
.btn-cancel:hover{background:#e2e8f0;}
.btn-confirm{flex:1;background:#ff4d4d;color:white;border:none;padding:12px;border-radius:8px;cursor:pointer;font-weight:700;font-size:14px;transition:.2s;}
.btn-confirm:hover{background:#e03030;}
</style>
</head>
<body>

<div class="top-bar">
    <div class="brand">The<strong>Desktop</strong> <span style="color:#8aa0c5;font-size:13px;font-weight:400;">Management & Analytics Portal</span></div>
    <div class="clock-bar" id="liveClock">--:--:-- --</div>
</div>

<?php if ($pc['status'] === 'active' && $sess): 
    $start = new DateTime($sess['start_time']);
    $now = new DateTime();
    $elapsed_secs = $now->getTimestamp() - $start->getTimestamp();
    $time_limit = $sess['time_limit']; // in minutes, null = open-ended
    $total_secs = $time_limit ? $time_limit * 60 : null;
    $remaining_secs = $total_secs ? max(0, $total_secs - $elapsed_secs) : null;
    $expired = $total_secs && $elapsed_secs >= $total_secs;

    $package = match(true) {
        $time_limit == 60  => '1 Hour Package',
        $time_limit == 180 => '3 Hours Package',
        $time_limit == 300 => '5 Hours Package',
        $time_limit == 420 => '7 Hours Package',
        $time_limit == 720 => '12 Hours Package',
        $time_limit == null => 'Open-Ended Session',
        default => $time_limit.' min Package'
    };
?>

<?php if ($expired): ?>
<!-- EXPIRED -->
<div class="expired-card">
    <div class="expired-icon"><i class="fas fa-clock"></i></div>
    <div class="pc-label"><?= htmlspecialchars($pc_name) ?></div>
    <div class="expired-title">Time's Up!</div>
    <div class="expired-sub">Your session has ended. Please settle your bill at the counter.</div>
    <div class="call-staff">
        <p><i class="fas fa-bell"></i> Please call the staff</p>
        <small>Your session will be closed and payment processed at the counter.</small>
    </div>
</div>

<?php else: ?>
<!-- ACTIVE SESSION -->
<div class="session-card" id="sessionCard">
    <div class="pc-label"><?= htmlspecialchars($pc_name) ?></div>
    <div class="pc-title"><i class="fas fa-desktop"></i> Your Session</div>

    <?php if ($total_secs): ?>
    <!-- Timer Ring -->
    <div class="timer-ring">
        <svg width="220" height="220" viewBox="0 0 220 220">
            <circle class="ring-bg" cx="110" cy="110" r="98"/>
            <circle class="ring-progress" id="ringProgress" cx="110" cy="110" r="98"
                stroke-dasharray="616"
                stroke-dashoffset="<?= 616 - (616 * $remaining_secs / $total_secs) ?>"/>
        </svg>
        <div class="timer-text">
            <div class="time-remaining" id="timeRemaining">
                <?= sprintf('%02d:%02d:%02d', floor($remaining_secs/3600), floor(($remaining_secs%3600)/60), $remaining_secs%60) ?>
            </div>
            <div class="time-label">Time Left</div>
        </div>
    </div>
    <?php else: ?>
    <!-- Open ended - show elapsed -->
    <div style="font-size:72px;font-family:monospace;font-weight:700;color:#38bdf8;margin-bottom:8px;" id="elapsedDisplay">
        <?= sprintf('%02d:%02d:%02d', floor($elapsed_secs/3600), floor(($elapsed_secs%3600)/60), $elapsed_secs%60) ?>
    </div>
    <div style="font-size:12px;color:#8aa0c5;text-transform:uppercase;letter-spacing:1px;margin-bottom:24px;">Elapsed Time</div>
    <?php endif; ?>

    <div class="package-badge"><i class="fas fa-tag"></i> <?= $package ?></div>
    <div class="time-elapsed" id="elapsedInfo">
        Started: <?= $start->format('g:i A') ?> &nbsp;|&nbsp; Elapsed: <span id="elapsedCount"><?= sprintf('%02d:%02d:%02d', floor($elapsed_secs/3600), floor(($elapsed_secs%3600)/60), $elapsed_secs%60) ?></span>
    </div>

    <br>
    <button class="end-btn" onclick="document.getElementById('confirmModal').classList.add('show')">
        <i class="fas fa-sign-out-alt"></i> End My Session
    </button>
</div>
<?php endif; ?>

<?php elseif ($pc['status'] === 'available'): ?>
<!-- IDLE -->
<div class="idle-card">
    <div class="idle-icon"><i class="fas fa-desktop"></i></div>
    <div class="pc-label"><?= htmlspecialchars($pc_name) ?></div>
    <div class="idle-title">PC Available</div>
    <div class="idle-sub">Please see the staff at the counter to start your session.</div>
</div>
<?php endif; ?>

<!-- Confirm End Modal -->
<div class="modal-bg" id="confirmModal">
    <div class="modal-box">
        <i class="fas fa-sign-out-alt"></i>
        <h3>End Your Session?</h3>
        <p>This will stop your timer. Please proceed to the counter to settle your bill.</p>
        <div class="modal-actions">
            <button class="btn-cancel" onclick="document.getElementById('confirmModal').classList.remove('show')">Stay</button>
            <button class="btn-confirm" onclick="endSession()">Yes, End It</button>
        </div>
    </div>
</div>

<script>
// Live clock
function updateClock(){
    const now=new Date();
    let h=now.getHours(),m=now.getMinutes(),s=now.getSeconds();
    const ampm=h>=12?'PM':'AM';h=h%12||12;
    document.getElementById('liveClock').textContent=String(h).padStart(2,'0')+':'+String(m).padStart(2,'0')+':'+String(s).padStart(2,'0')+' '+ampm;
}
updateClock();setInterval(updateClock,1000);

<?php if($pc['status']==='active' && $sess && !$expired): ?>
let remainingSecs = <?= $remaining_secs ?? 'null' ?>;
let elapsedSecs = <?= $elapsed_secs ?>;
const totalSecs = <?= $total_secs ?? 'null' ?>;
const circumference = 616;

function pad(n){return String(n).padStart(2,'0');}
function formatTime(s){return pad(Math.floor(s/3600))+':'+pad(Math.floor((s%3600)/60))+':'+pad(s%60);}

setInterval(()=>{
    elapsedSecs++;
    // Update elapsed
    const ec=document.getElementById('elapsedCount');
    if(ec) ec.textContent=formatTime(elapsedSecs);
    const ed=document.getElementById('elapsedDisplay');
    if(ed) ed.textContent=formatTime(elapsedSecs);

    if(totalSecs){
        remainingSecs=Math.max(0,totalSecs-elapsedSecs);
        document.getElementById('timeRemaining').textContent=formatTime(remainingSecs);

        // Ring progress
        const offset=circumference-(circumference*(remainingSecs/totalSecs));
        const ring=document.getElementById('ringProgress');
        ring.style.strokeDashoffset=offset;

        // Color changes
        const pct=remainingSecs/totalSecs;
        if(pct<=0.1) ring.classList.add('ring-expired');
        else if(pct<=0.25) ring.classList.add('ring-warning');

        // Expired - reload to show expired screen
        if(remainingSecs<=0) location.reload();
    }
},1000);
<?php endif; ?>

function endSession(){
    window.location.href='end_session.php?id=<?= $pc['id'] ?>&redirect=<?= urlencode("session_display.php?pc=".$pc_name) ?>';
}
</script>
</body>
</html>

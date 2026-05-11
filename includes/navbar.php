<?php
$current = basename($_SERVER['PHP_SELF']);
?>
<style>
.alarm-bar{
    display:none;
    position:fixed;top:60px;left:0;right:0;
    background:linear-gradient(90deg,#6b0000,#ff0000,#ff4500,#ff0000,#6b0000);
    background-size:300% 100%;
    color:#fff;
    padding:14px 24px;
    text-align:center;
    font-weight:800;
    font-size:15px;
    z-index:4999;
    letter-spacing:1.5px;
    border-bottom:3px solid #ff4d4d;
    box-shadow:0 4px 20px rgba(255,0,0,.5);
    animation:alarmSlide 2s linear infinite, alarmFade .6s ease-in-out infinite alternate;
    cursor:pointer;
}
.alarm-bar.show{display:block;}
.alarm-bar i{margin:0 8px;font-size:16px;}
@keyframes alarmSlide{
    0%{background-position:0% 50%}
    100%{background-position:300% 50%}
}
@keyframes alarmFade{
    from{opacity:.85;}
    to{opacity:1;}
}
/* Push page content down when alarm is visible */
body.alarm-visible .page-wrap,
body.alarm-visible .wrap,
body.alarm-visible .container,
body.alarm-visible main {
    margin-top: 48px !important;
}
</style>

<nav class="navbar">
    <div class="nav-brand">
        <i class="fas fa-clock"></i>
        <span>The<strong>Desktop</strong></span>
    </div>
    <div class="nav-links">
        <a href="counter.php" class="<?= $current=='counter.php'?'active':'' ?>">
            <i class="fas fa-list"></i> Counter
        </a>
        <a href="printing.php" class="<?= $current=='printing.php'?'active':'' ?>">
            <i class="fas fa-print"></i> Printing
        </a>
        <a href="dashboard.php" class="<?= $current=='dashboard.php'?'active':'' ?>">
            <i class="fas fa-chart-pie"></i> Dashboard
        </a>
        <a href="settings.php" class="<?= $current=='settings.php'?'active':'' ?>">
            <i class="fas fa-cog"></i> Settings
        </a>
        <a href="analytics.php" class="<?= $current=='analytics.php'?'active':'' ?>" style="<?= $current=='analytics.php'?'':'color:#2ecc71;' ?>">
            <i class="fas fa-chart-line"></i> Analytics
        </a>
    </div>
    <div class="nav-right">
        <span class="nav-time" id="navTime"></span>
        <span class="nav-user"><i class="fas fa-user"></i> <?= htmlspecialchars($display_user ?? '') ?></span>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
</nav>

<!-- Global Overtime Alarm Bar (shown on ALL pages) -->
<div class="alarm-bar" id="globalAlarmBar" onclick="window.location.href='counter.php'">
    <i class="fas fa-exclamation-triangle"></i>
    ⚠ OVERTIME ALERT — One or more PCs have exceeded their time limit! Click here to attend to them.
    <i class="fas fa-exclamation-triangle"></i>
</div>

<script>
// Clock
function updateTime() {
    const now = new Date();
    let h = now.getHours(), m = now.getMinutes(), s = now.getSeconds();
    const ampm = h >= 12 ? 'PM' : 'AM';
    h = h % 12 || 12;
    document.getElementById('navTime').textContent =
        String(h).padStart(2,'0') + ':' + String(m).padStart(2,'0') + ':' + String(s).padStart(2,'0') + ' ' + ampm;
}
updateTime();
setInterval(updateTime, 1000);

// Overtime polling — checks every 10 seconds from any page
let _alarmAudio = null;
let _alarmPlaying = false;

function beep() {
    if (!_alarmPlaying) return;
    try {
        const ctx = new (window.AudioContext || window.webkitAudioContext)();
        const o = ctx.createOscillator(), g = ctx.createGain();
        o.connect(g); g.connect(ctx.destination);
        o.type = 'square'; o.frequency.value = 900;
        g.gain.setValueAtTime(0.25, ctx.currentTime);
        g.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.3);
        o.start(); o.stop(ctx.currentTime + 0.3);
        setTimeout(beep, 2500);
    } catch(e) {}
}

function checkOvertime() {
    fetch('check_overtime.php')
        .then(r => r.json())
        .then(d => {
            const bar = document.getElementById('globalAlarmBar');
            if (d.overtime) {
                bar.classList.add('show');
                document.body.classList.add('alarm-visible');
                if (!_alarmPlaying) {
                    _alarmPlaying = true;
                    beep();
                }
                // Update text with count
                const cnt = d.count;
                bar.innerHTML = '<i class="fas fa-exclamation-triangle"></i>'
                    + ' ⚠ OVERTIME ALERT — ' + cnt + ' PC' + (cnt>1?'s are':' is') + ' past the time limit! Click here to attend.'
                    + ' <i class="fas fa-exclamation-triangle"></i>';
            } else {
                bar.classList.remove('show');
                document.body.classList.remove('alarm-visible');
                _alarmPlaying = false;
            }
        })
        .catch(() => {});
}

// Check immediately + every 10s
checkOvertime();
setInterval(checkOvertime, 10000);
</script>

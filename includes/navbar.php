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
    animation:alarmSlide 2s linear infinite;
    cursor:pointer;
    user-select:none;
}
.alarm-bar.show{display:block;}
.alarm-bar i{margin:0 8px;}
@keyframes alarmSlide{
    0%{background-position:0% 50%}
    100%{background-position:300% 50%}
}
body.alarm-visible .page-wrap,
body.alarm-visible .wrap,
body.alarm-visible .container,
body.alarm-visible main{margin-top:48px !important;}
</style>

<nav class="navbar">
    <div class="nav-brand">
        <i class="fas fa-clock"></i>
        <span>The<strong>Desktop</strong></span>
    </div>
    <div class="nav-links">
        <a href="counter.php"   class="<?= $current=='counter.php'  ?'active':'' ?>"><i class="fas fa-list"></i> Counter</a>
        <a href="printing.php"  class="<?= $current=='printing.php' ?'active':'' ?>"><i class="fas fa-print"></i> Printing</a>
        <a href="dashboard.php" class="<?= $current=='dashboard.php'?'active':'' ?>"><i class="fas fa-chart-pie"></i> Dashboard</a>
        <a href="settings.php"  class="<?= $current=='settings.php' ?'active':'' ?>"><i class="fas fa-cog"></i> Settings</a>
        <a href="analytics.php" class="<?= $current=='analytics.php'?'active':'' ?>" style="<?= $current=='analytics.php'?'':'color:#2ecc71;' ?>"><i class="fas fa-chart-line"></i> Analytics</a>
    </div>
    <div class="nav-right">
        <span class="nav-time" id="navTime"></span>
        <span class="nav-user"><i class="fas fa-user"></i> <?= htmlspecialchars($display_user ?? '') ?></span>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
</nav>

<!-- Global Overtime Alarm Bar -->
<div class="alarm-bar" id="globalAlarmBar" onclick="window.location.href='counter.php'">
    <i class="fas fa-exclamation-triangle"></i>
    ⚠ OVERTIME ALERT — <span id="overtimeMsg">One or more PCs have exceeded their time limit!</span>
    Click here to attend.
    <i class="fas fa-exclamation-triangle"></i>
</div>

<script>
// ── Clock ─────────────────────────────────────────────────────────────────
(function(){
    function tick(){
        var n=new Date(),h=n.getHours(),m=n.getMinutes(),s=n.getSeconds();
        var ap=h>=12?'PM':'AM'; h=h%12||12;
        var el=document.getElementById('navTime');
        if(el) el.textContent=(h<10?'0'+h:h)+':'+(m<10?'0'+m:m)+':'+(s<10?'0'+s:s)+' '+ap;
    }
    tick(); setInterval(tick,1000);
})();

// ── Audio — exact copy of working counter.php logic ─────────────────────
let alarmPlaying = false;

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

// ── Overtime polling ──────────────────────────────────────────────────────
let anyOvertime = false;

function checkOvertime(){
    var url = window.location.origin + '/check_overtime.php';
    fetch(url, {cache:'no-store'})
        .then(r => r.json())
        .then(d => {
            const bar = document.getElementById('globalAlarmBar');
            const msg = document.getElementById('overtimeMsg');
            if (d.overtime) {
                anyOvertime = true;
                const cnt = d.count;
                if (msg) msg.textContent = cnt + ' PC' + (cnt > 1 ? 's are' : ' is') + ' past the time limit!';
                bar.classList.add('show');
                document.body.classList.add('alarm-visible');
            } else {
                anyOvertime = false;
                alarmPlaying = false;
                bar.classList.remove('show');
                document.body.classList.remove('alarm-visible');
            }
        })
        .catch(e => console.warn('Overtime check error:', e));
}

// Mirror the exact setInterval pattern from counter.php
setInterval(() => {
    if (anyOvertime) {
        if (!alarmPlaying) {
            alarmPlaying = true;
            beep();
        }
    }
}, 1000);

checkOvertime();
setInterval(checkOvertime, 8000);
</script>

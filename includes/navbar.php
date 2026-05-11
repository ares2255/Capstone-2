<?php
$current = basename($_SERVER['PHP_SELF']);
// Build absolute base path so fetch works from any page
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
// If navbar is included from a subfolder, go up one level
if (strpos($current, '/includes/') !== false) {
    $base = dirname($base);
}
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
.alarm-bar i{margin:0 8px;font-size:16px;}
@keyframes alarmSlide{
    0%{background-position:0% 50%}
    100%{background-position:300% 50%}
}
body.alarm-visible .page-wrap,
body.alarm-visible .wrap,
body.alarm-visible .container,
body.alarm-visible main {
    margin-top:48px !important;
}
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
    ⚠ OVERTIME ALERT — One or more PCs have exceeded their time limit! Click here to attend to them.
    <i class="fas fa-exclamation-triangle"></i>
</div>

<!-- Hidden audio beep (works without user gesture on most browsers) -->
<audio id="overtimeAudio" preload="auto" style="display:none">
    <source id="overtimeAudioSrc" src="" type="audio/mpeg">
</audio>

<script>
// ── Clock ────────────────────────────────────────────────────────────────────
function updateTime(){
    const now=new Date();
    let h=now.getHours(),m=now.getMinutes(),s=now.getSeconds();
    const ap=h>=12?'PM':'AM'; h=h%12||12;
    document.getElementById('navTime').textContent=
        String(h).padStart(2,'0')+':'+String(m).padStart(2,'0')+':'+String(s).padStart(2,'0')+' '+ap;
}
updateTime(); setInterval(updateTime,1000);

// ── Audio beep via WebAudio (no file needed) ──────────────────────────────────
let _audioCtx = null;
let _alarmPlaying = false;
let _beepScheduled = false;

function getAudioCtx(){
    if(!_audioCtx){
        try { _audioCtx = new (window.AudioContext||window.webkitAudioContext)(); } catch(e){}
    }
    // Resume if suspended (browser autoplay policy)
    if(_audioCtx && _audioCtx.state === 'suspended'){
        _audioCtx.resume();
    }
    return _audioCtx;
}

// Unlock audio on ANY user interaction with the page
['click','keydown','touchstart'].forEach(evt=>{
    document.addEventListener(evt, function unlock(){
        getAudioCtx();
        document.removeEventListener(evt, unlock);
    }, {once:true});
});

function playBeep(){
    if(!_alarmPlaying) return;
    const ctx = getAudioCtx();
    if(!ctx) return;
    try {
        const o=ctx.createOscillator(), g=ctx.createGain();
        o.connect(g); g.connect(ctx.destination);
        o.type='square'; o.frequency.value=880;
        g.gain.setValueAtTime(0.3, ctx.currentTime);
        g.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime+0.4);
        o.start(ctx.currentTime);
        o.stop(ctx.currentTime+0.4);
    } catch(e){}
    setTimeout(playBeep, 3000);
}

// ── Overtime polling ─────────────────────────────────────────────────────────
// Use absolute path so it works from any page
const _overtimeUrl = '/check_overtime.php';

function checkOvertime(){
    fetch(_overtimeUrl)
        .then(r=>r.json())
        .then(d=>{
            const bar = document.getElementById('globalAlarmBar');
            if(d.overtime){
                const cnt = d.count;
                bar.innerHTML = '<i class="fas fa-exclamation-triangle"></i>'
                    +' ⚠ OVERTIME ALERT — '+cnt+' PC'+(cnt>1?' are':' is')+' past the time limit! Click here to attend.'
                    +' <i class="fas fa-exclamation-triangle"></i>';
                bar.classList.add('show');
                document.body.classList.add('alarm-visible');
                if(!_alarmPlaying){
                    _alarmPlaying = true;
                    playBeep();
                }
            } else {
                bar.classList.remove('show');
                document.body.classList.remove('alarm-visible');
                _alarmPlaying = false;
            }
        })
        .catch(()=>{});
}

checkOvertime();
setInterval(checkOvertime, 8000);
</script>

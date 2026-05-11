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

<script>
// ── Clock ─────────────────────────────────────────────────────────────────────
(function(){
    function tick(){
        var n=new Date(),h=n.getHours(),m=n.getMinutes(),s=n.getSeconds();
        var ap=h>=12?'PM':'AM'; h=h%12||12;
        var el=document.getElementById('navTime');
        if(el) el.textContent=(h<10?'0'+h:h)+':'+(m<10?'0'+m:m)+':'+(s<10?'0'+s:s)+' '+ap;
    }
    tick(); setInterval(tick,1000);
})();

// ── Beep via Web Audio ────────────────────────────────────────────────────────
var _ctx=null, _alarmOn=false;

function _getCtx(){
    if(!_ctx){ try{_ctx=new(window.AudioContext||window.webkitAudioContext)();}catch(e){} }
    if(_ctx && _ctx.state==='suspended') _ctx.resume();
    return _ctx;
}

// Unlock audio on first user interaction
document.addEventListener('click',function u(){_getCtx();document.removeEventListener('click',u);},{capture:true,once:true});
document.addEventListener('keydown',function u(){_getCtx();document.removeEventListener('keydown',u);},{capture:true,once:true});

function _doBeep(){
    if(!_alarmOn) return;
    var ctx=_getCtx(); if(!ctx){setTimeout(_doBeep,3000);return;}
    try{
        var o=ctx.createOscillator(),g=ctx.createGain();
        o.connect(g);g.connect(ctx.destination);
        o.type='square';o.frequency.value=880;
        g.gain.setValueAtTime(0.35,ctx.currentTime);
        g.gain.exponentialRampToValueAtTime(0.0001,ctx.currentTime+0.45);
        o.start(ctx.currentTime);o.stop(ctx.currentTime+0.45);
    }catch(e){}
    setTimeout(_doBeep,3000);
}

// ── Overtime polling ──────────────────────────────────────────────────────────
// Use window.location.origin so URL is ALWAYS correct on any page/host
function checkOvertime(){
    var url = window.location.origin + '/check_overtime.php';
    fetch(url, {cache:'no-store'})
        .then(function(r){ return r.json(); })
        .then(function(d){
            var bar=document.getElementById('globalAlarmBar');
            if(d.overtime){
                var cnt=d.count;
                bar.innerHTML='<i class="fas fa-exclamation-triangle"></i>'
                    +' ⚠ OVERTIME ALERT — '+cnt+' PC'+(cnt>1?'s are':' is')+' past the time limit! Click here to attend.'
                    +' <i class="fas fa-exclamation-triangle"></i>';
                bar.classList.add('show');
                document.body.classList.add('alarm-visible');
                if(!_alarmOn){ _alarmOn=true; _doBeep(); }
            } else {
                bar.classList.remove('show');
                document.body.classList.remove('alarm-visible');
                _alarmOn=false;
            }
        })
        .catch(function(e){ console.warn('Overtime check error:', e); });
}

checkOvertime();
setInterval(checkOvertime, 8000);
</script>

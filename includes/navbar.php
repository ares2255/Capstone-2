<?php
$current = basename($_SERVER['PHP_SELF']);
?>
<!-- Global Overtime Bar -->
<div class="overtime-bar" id="overtimeBar">
    <div class="overtime-bar-inner">
        <span class="ot-icon">⚠</span>
        <span id="overtimeBarText">OVERTIME ALERT!</span>
        <a href="counter.php" class="ot-link">→ Go to Counter</a>
        <button class="ot-sound-btn" onclick="forceSoundOn(this)">🔊 Enable Sound</button>
        <span class="ot-icon">⚠</span>
    </div>
</div>

<nav class="navbar">
    <div class="nav-brand">
        <i class="fas fa-desktop"></i>
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
        <span class="ot-nav-badge" id="otNavBadge" style="display:none;">
            <i class="fas fa-exclamation-triangle"></i>
            <span id="otNavCount">0</span> OVERTIME
        </span>
        <span class="nav-time" id="navTime"></span>
        <span class="nav-user"><i class="fas fa-user"></i> <?= htmlspecialchars($display_user ?? '') ?></span>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
</nav>

<script>
// ── Clock ──
function updateTime(){
    const now=new Date();
    let h=now.getHours(),m=now.getMinutes(),s=now.getSeconds();
    const ampm=h>=12?'PM':'AM'; h=h%12||12;
    document.getElementById('navTime').textContent=
        String(h).padStart(2,'0')+':'+String(m).padStart(2,'0')+':'+String(s).padStart(2,'0')+' '+ampm;
}
updateTime(); setInterval(updateTime,1000);

// ── Sound ──
let _ctx=null, _alarming=false;

function _getCtx(){
    if(!_ctx) _ctx=new(window.AudioContext||window.webkitAudioContext)();
    return _ctx;
}

function _beep(){
    if(!_alarming) return;
    try{
        const ctx=_getCtx();
        if(ctx.state==='suspended') ctx.resume();
        const o=ctx.createOscillator(), g=ctx.createGain();
        o.connect(g); g.connect(ctx.destination);
        o.type='square'; o.frequency.value=880;
        g.gain.setValueAtTime(0.3,ctx.currentTime);
        g.gain.exponentialRampToValueAtTime(0.001,ctx.currentTime+0.4);
        o.start(); o.stop(ctx.currentTime+0.4);
        setTimeout(_beep, 3000);
    }catch(e){}
}

function forceSoundOn(btn){
    _alarming=true;
    _beep();
    if(btn){ btn.textContent='🔊 Sounding'; btn.disabled=true; }
}

// Auto-start sound on first interaction
function _trySound(){
    if(_alarming && _ctx && _ctx.state==='suspended') _ctx.resume();
}
document.addEventListener('click', _trySound);
document.addEventListener('keydown', _trySound);

// ── Overtime Checker ──
function checkOvertime(){
    fetch('check_overtime.php?t='+Date.now())
    .then(r=>r.json())
    .then(d=>{
        const bar  = document.getElementById('overtimeBar');
        const text = document.getElementById('overtimeBarText');
        const badge= document.getElementById('otNavBadge');
        const cnt  = document.getElementById('otNavCount');
        if(d.count>0){
            bar.classList.add('show');
            badge.style.display='flex';
            cnt.textContent=d.count;
            text.textContent='OVERTIME — '+d.names.join(', ')+' exceeded time limit!';
            if(!_alarming){ _alarming=true; _beep(); }
        } else {
            bar.classList.remove('show');
            badge.style.display='none';
            _alarming=false;
        }
    })
    .catch(()=>{});
}

checkOvertime();
setInterval(checkOvertime, 10000);
</script>

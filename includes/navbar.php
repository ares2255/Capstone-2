<?php
$current = basename($_SERVER['PHP_SELF']);
?>
<!-- Theme CSS (global light/dark) -->
<link rel="stylesheet" href="includes/theme.css">
<!-- Apply saved theme BEFORE render to avoid flash -->
<script>
(function(){
    if(localStorage.getItem('settings_theme') === 'light'){
        document.documentElement.classList.add('light-mode-pre');
    }
})();
</script>
<style>
html.light-mode-pre body { background: linear-gradient(135deg,#e8edf5 0%,#f0f4fb 50%,#e4ecf7 100%) !important; }
</style>
<!-- Global Overtime Bar -->
<div class="overtime-bar" id="overtimeBar">
    <div class="overtime-bar-inner">
        <span class="ot-icon">⚠</span>
        <span id="overtimeBarText">OVERTIME ALERT!</span>
        <a href="counter.php" class="ot-link">→ Go to Counter</a>
        <button class="ot-sound-btn" id="otSoundBtn" onclick="toggleSound()">🔊 Sound On</button>
        <span class="ot-icon">⚠</span>
    </div>
</div>

<nav class="navbar">
    <div class="nav-brand">
        <img src="logo.jpg" alt="Q Solutions" class="nav-logo-img">
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
let _muted = localStorage.getItem('ot_muted') === 'true';

function _getCtx(){
    if(!_ctx) _ctx=new(window.AudioContext||window.webkitAudioContext)();
    return _ctx;
}

function _beep(){
    if(!_alarming || _muted) return;
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

function toggleSound(){
    _muted = !_muted;
    localStorage.setItem('ot_muted', _muted);
    const btn = document.getElementById('otSoundBtn');
    if(_muted){
        btn.textContent = '🔇 Sound Off';
        btn.style.background = 'rgba(0,0,0,0.3)';
    } else {
        btn.textContent = '🔊 Sound On';
        btn.style.background = '';
        _beep();
    }
}

// Apply saved mute state to button once bar is visible
function _applyMuteBtn(){
    const btn = document.getElementById('otSoundBtn');
    if(!btn) return;
    if(_muted){
        btn.textContent = '🔇 Sound Off';
        btn.style.background = 'rgba(0,0,0,0.3)';
    } else {
        btn.textContent = '🔊 Sound On';
        btn.style.background = '';
    }
}

document.addEventListener('click', function(){
    if(_alarming && !_muted && _ctx && _ctx.state==='suspended') _ctx.resume();
});

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
            _applyMuteBtn();
            if(!_alarming){
                _alarming=true;
                if(!_muted) _beep();
            }
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

// ── Theme: apply saved preference on every page ──
(function(){
    const saved = localStorage.getItem('settings_theme') || 'dark';
    if(saved === 'light'){
        document.body.classList.add('light-mode');
    } else {
        document.body.classList.remove('light-mode');
    }
    document.documentElement.classList.remove('light-mode-pre');
})();
</script>

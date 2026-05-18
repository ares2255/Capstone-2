<?php
$current = basename($_SERVER['PHP_SELF']);
?>
<!-- LIGHT MODE CSS — inline so it always loads on every page -->
<style id="light-mode-css">
html.light-mode body{background:linear-gradient(135deg,#e8edf5 0%,#f0f4fb 50%,#e4ecf7 100%)!important;color:#1e293b!important}
html.light-mode .navbar{background:#ffffff!important;border-bottom:2px solid #d1dce8!important;box-shadow:0 2px 12px rgba(30,42,120,.08)!important}
html.light-mode .nav-links a{color:#4a5f7a!important}
html.light-mode .nav-links a:hover{background:#eef2fb!important;color:#1e2a78!important}
html.light-mode .nav-links a.active{background:#1e2a78!important;color:white!important}
html.light-mode .nav-time{background:#eef2fb!important;color:#1e2a78!important}
html.light-mode .nav-user{color:#4a5f7a!important}
html.light-mode .nav-btn-logout{background:#1e2a78!important;color:white!important}
html.light-mode .card,html.light-mode .stat-card,html.light-mode .chart-card,html.light-mode .panel-card,html.light-mode .table-container{background:#ffffff!important;border-color:#d1dce8!important;box-shadow:0 4px 20px rgba(30,42,120,.07)!important}
html.light-mode .card h3,html.light-mode .chart-card h3,html.light-mode .table-header h3{color:#1e2a78!important;border-bottom-color:#dde5f0!important}
html.light-mode .stat-box{background:#ffffff!important;border-color:#d1dce8!important;box-shadow:0 4px 16px rgba(30,42,120,.07)!important}
html.light-mode .stat-info h3,html.light-mode h2,html.light-mode h3{color:#1e293b!important}
html.light-mode .stat-info p,html.light-mode .stat-lbl,html.light-mode small,html.light-mode .text-muted{color:#64748b!important}
html.light-mode .page-header p{color:#4a5f7a!important}
html.light-mode .pc-card{background:#ffffff!important;border:2px solid #a0b4d0!important;box-shadow:0 4px 16px rgba(30,42,120,.18)!important}
html.light-mode .pc-card:hover{background:#f0f5ff!important;border-color:#1e2a78!important;box-shadow:0 8px 28px rgba(30,42,120,.25)!important}
html.light-mode .pc-name,.pc-card .timer-avail{color:#1e293b!important}
html.light-mode .action-hint{color:#64748b!important}
html.light-mode input[type="number"],html.light-mode input[type="text"],html.light-mode input[type="password"],html.light-mode input[type="date"],html.light-mode input[type="email"],html.light-mode textarea{background:#f4f7fb!important;border-color:#c8d5e8!important;color:#1e293b!important}
html.light-mode select{background:#f4f7fb!important;border-color:#c8d5e8!important;color:#1e293b!important}
html.light-mode select option{background:#f4f7fb!important;color:#1e293b!important}
html.light-mode select option:checked{background:#1e2a78!important;color:white!important}
html.light-mode .date-bar,html.light-mode .date-filter{background:#ffffff!important;border-color:#d1dce8!important}
html.light-mode .date-bar label,html.light-mode .date-filter label{color:#64748b!important}
html.light-mode .date-banner{background:rgba(30,42,120,.06)!important;border-color:rgba(30,42,120,.15)!important;color:#1e2a78!important}
html.light-mode .date-banner.today{background:rgba(22,163,74,.06)!important;color:#16a34a!important}
html.light-mode table th{color:#4a5f7a!important;border-bottom-color:#dde5f0!important}
html.light-mode table td{border-bottom-color:#edf1f7!important;color:#1e293b!important}
html.light-mode .record-count{background:#eef2fb!important;color:#4a5f7a!important}
html.light-mode .no-records{color:#64748b!important}
html.light-mode .toggle-row{background:#f0f4fb!important;border-color:#d1dce8!important}
html.light-mode .toggle-btn{color:#4a5f7a!important}
html.light-mode .toggle-btn.active{background:#1e2a78!important;color:white!important}
html.light-mode .price-preview{background:#f4f7fb!important;border-color:#d1dce8!important;color:#1e293b!important}
html.light-mode .pkg-badge,html.light-mode .type-badge{background:rgba(30,42,120,.08)!important;border-color:rgba(30,42,120,.2)!important;color:#1e2a78!important}
html.light-mode .pkg-price,html.light-mode .price-text{color:#16a34a!important}
html.light-mode .pkg-mins,html.light-mode .pkg-empty,html.light-mode .pkg-add-row label,html.light-mode .pkg-table th{color:#64748b!important}
html.light-mode .pkg-table td{color:#1e293b!important;border-bottom-color:#edf1f7!important}
html.light-mode .pkg-add-row{border-bottom-color:#dde5f0!important}
html.light-mode .pkg-add-row input{background:#f4f7fb!important;border-color:#c8d5e8!important;color:#1e293b!important}
html.light-mode .input-group label,html.light-mode .section-label{color:#4a5f7a!important}
html.light-mode .compare-item{background:#ffffff!important;border-color:#d1dce8!important}
html.light-mode .compare-item .label{color:#64748b!important}
html.light-mode .alert-success{background:rgba(22,163,74,.08)!important;color:#16a34a!important;border-color:rgba(22,163,74,.25)!important}
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
        <a href="counter.php" class="<?= $current=='counter.php'?'active':'' ?>" onclick="navGo(event,'counter.php')">
            <i class="fas fa-list"></i> Counter
        </a>
        <a href="printing.php" class="<?= $current=='printing.php'?'active':'' ?>" onclick="navGo(event,'printing.php')">
            <i class="fas fa-print"></i> Printing
        </a>
        <a href="dashboard.php" class="<?= $current=='dashboard.php'?'active':'' ?>" onclick="navGo(event,'dashboard.php')">
            <i class="fas fa-chart-pie"></i> Dashboard
        </a>
        <a href="settings.php" class="<?= $current=='settings.php'?'active':'' ?>" onclick="navGo(event,'settings.php')">
            <i class="fas fa-cog"></i> Settings
        </a>
        <a href="analytics.php" class="<?= $current=='analytics.php'?'active':'' ?>" style="<?= $current=='analytics.php'?'':'color:#2ecc71;' ?>" onclick="navGo(event,'analytics.php')">
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
// ── Clock (no seconds) ──
function updateTime(){
    const now=new Date();
    let h=now.getHours(),m=now.getMinutes();
    const ampm=h>=12?'PM':'AM'; h=h%12||12;
    document.getElementById('navTime').textContent=
        String(h).padStart(2,'0')+':'+String(m).padStart(2,'0')+' '+ampm;
}
updateTime(); setInterval(updateTime,1000);

// ── Sound ──
let _ctx=null, _alarming=false, _beepTimer=null;
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
        _beepTimer = setTimeout(_beep, 3000);
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

// Fully kill alarm — called by counter.php when a session ends
function stopAlarmNow(){
    _alarming = false;
    if(_beepTimer){ clearTimeout(_beepTimer); _beepTimer=null; }
    const bar   = document.getElementById('overtimeBar');
    const badge = document.getElementById('otNavBadge');
    if(bar)   bar.classList.remove('show');
    if(badge) badge.style.display='none';
}

// ── Instant nav: cancel polling before navigating ──
function navGo(e, url){
    e.preventDefault();
    if(typeof _otController !== 'undefined' && _otController) _otController.abort();
    if(typeof _otInterval !== 'undefined') clearInterval(_otInterval);
    window.location.href = url;
}

// ── Overtime Checker ──
let _otController = null;
function checkOvertime(){
    if(_otController) _otController.abort();
    _otController = new AbortController();
    fetch('check_overtime.php?t='+Date.now(), {signal: _otController.signal})
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
            if(_beepTimer){ clearTimeout(_beepTimer); _beepTimer=null; }
        }
    })
    .catch(err=>{ if(err.name !== 'AbortError') console.warn(err); });
}

checkOvertime();
let _otInterval = setInterval(checkOvertime, 10000);
</script>

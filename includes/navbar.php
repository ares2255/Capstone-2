<?php
$current = basename($_SERVER['PHP_SELF']);
?>
<div class="global-overtime-bar" id="globalOvertimeBar" style="display:none;">
    <span>⚠</span>
    <span id="overtimeBarText">OVERTIME ALERT!</span>
    <a href="counter.php" class="overtime-link">Go to Counter →</a>
    <span>⚠</span>
</div>
<nav class="navbar">
    <a href="counter.php" class="nav-brand">
        <i class="fas fa-desktop"></i>
        <span>The<strong>Desktop</strong></span>
    </a>
    <div class="nav-links">
        <a href="counter.php" class="<?= $current=='counter.php'?'active':'' ?>"><i class="fas fa-list"></i> Counter</a>
        <a href="printing.php" class="<?= $current=='printing.php'?'active':'' ?>"><i class="fas fa-print"></i> Printing</a>
        <a href="dashboard.php" class="<?= $current=='dashboard.php'?'active':'' ?>"><i class="fas fa-chart-pie"></i> Dashboard</a>
        <a href="settings.php" class="<?= $current=='settings.php'?'active':'' ?>"><i class="fas fa-cog"></i> Settings</a>
        <a href="analytics.php" class="<?= $current=='analytics.php'?'active':'' ?>" style="<?= $current=='analytics.php'?'':'color:#2ecc71;' ?>"><i class="fas fa-chart-line"></i> Analytics</a>
    </div>
    <div class="nav-right">
        <span class="nav-overtime-badge" id="navOvertimeBadge" style="display:none;">
            <i class="fas fa-exclamation-triangle"></i> <span id="navOvertimeCount">0</span> OVERTIME
        </span>
        <span class="nav-time" id="navTime"></span>
        <span class="nav-user"><i class="fas fa-user"></i> <?= htmlspecialchars($display_user ?? '') ?></span>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
</nav>
<script>
// Clock
function updateTime(){
    const now=new Date();
    let h=now.getHours(),m=now.getMinutes(),s=now.getSeconds();
    const ampm=h>=12?'PM':'AM';
    h=h%12||12;
    document.getElementById('navTime').textContent=
        String(h).padStart(2,'0')+':'+String(m).padStart(2,'0')+':'+String(s).padStart(2,'0')+' '+ampm;
}
updateTime(); setInterval(updateTime,1000);

// Alarm sound
let _alarmGoing=false;
function _beep(){
    try{
        const ctx=new(window.AudioContext||window.webkitAudioContext)();
        const o=ctx.createOscillator(),g=ctx.createGain();
        o.connect(g);g.connect(ctx.destination);
        o.type='square';o.frequency.value=880;
        g.gain.setValueAtTime(0.3,ctx.currentTime);
        g.gain.exponentialRampToValueAtTime(0.001,ctx.currentTime+0.4);
        o.start();o.stop(ctx.currentTime+0.4);
        setTimeout(_beep,3000);
    }catch(e){}
}

// Show banner + start sound
function triggerOvertimeAlert(names){
    const bar=document.getElementById('globalOvertimeBar');
    const badge=document.getElementById('navOvertimeBadge');
    const count=document.getElementById('navOvertimeCount');
    const text=document.getElementById('overtimeBarText');
    bar.style.display='flex';
    badge.style.display='flex';
    count.textContent=names.length;
    text.textContent='OVERTIME — '+names.join(', ')+' exceeded time limit!';
    if(!_alarmGoing){_alarmGoing=true;_beep();}
}

// Poll check_overtime.php every 10 seconds
function checkOvertime(){
    fetch('check_overtime.php?t='+Date.now())
        .then(r=>{
            if(!r.ok) throw new Error('HTTP '+r.status);
            return r.json();
        })
        .then(d=>{
            console.log('Overtime check:', d);
            if(d.count>0) triggerOvertimeAlert(d.names);
            else {
                document.getElementById('globalOvertimeBar').style.display='none';
                document.getElementById('navOvertimeBadge').style.display='none';
            }
        })
        .catch(e=>console.error('Overtime check failed:',e));
}
checkOvertime();
setInterval(checkOvertime,10000);
</script>

<?php
$current = basename($_SERVER['PHP_SELF']);
$overtimeCount = 0;
$overtimePCs = [];
try {
    if (!isset($pdo)) {
        $dbPath = __DIR__ . '/../config/db.php';
        if (file_exists($dbPath)) include $dbPath;
    }
    if (isset($pdo)) {
        $stmt = $pdo->query("
            SELECT p.name,
                   EXTRACT(EPOCH FROM (NOW() - s.start_time))/60 AS elapsed_mins,
                   s.time_limit
            FROM sessions s
            JOIN pcs p ON p.id = s.pc_id
            WHERE s.end_time IS NULL
              AND s.time_limit IS NOT NULL
              AND s.time_limit > 0
              AND EXTRACT(EPOCH FROM (NOW() - s.start_time))/60 > s.time_limit
        ");
        foreach ($stmt->fetchAll() as $row) {
            $overtimeCount++;
            $overtimePCs[] = htmlspecialchars($row['name']);
        }
    }
} catch (Exception $e) {}
?>
<?php if ($overtimeCount > 0): ?>
<div class="global-overtime-bar">
    <span>⚠</span>
    OVERTIME — <?= implode(', ', $overtimePCs) ?> exceeded time limit!
    <a href="counter.php" class="overtime-link">Go to Counter →</a>
    <span>⚠</span>
</div>
<?php endif; ?>
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
        <?php if ($overtimeCount > 0): ?>
        <span class="nav-overtime-badge"><i class="fas fa-exclamation-triangle"></i> <?= $overtimeCount ?> OVERTIME</span>
        <?php endif; ?>
        <span class="nav-time" id="navTime"></span>
        <span class="nav-user"><i class="fas fa-user"></i> <?= htmlspecialchars($display_user ?? '') ?></span>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
</nav>
<script>
function updateTime(){
    const now=new Date();
    let h=now.getHours(),m=now.getMinutes(),s=now.getSeconds();
    const ampm=h>=12?'PM':'AM';
    h=h%12||12;
    document.getElementById('navTime').textContent=
        String(h).padStart(2,'0')+':'+String(m).padStart(2,'0')+':'+String(s).padStart(2,'0')+' '+ampm;
}
updateTime(); setInterval(updateTime,1000);

<?php if ($overtimeCount > 0): ?>
// Overtime alarm sound
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
function _startAlarm(){
    if(!_alarmGoing){_alarmGoing=true;_beep();}
    document.removeEventListener('click',_startAlarm);
    document.removeEventListener('keydown',_startAlarm);
}
document.addEventListener('click',_startAlarm);
document.addEventListener('keydown',_startAlarm);
setTimeout(()=>{if(!_alarmGoing)_startAlarm();},500);
<?php endif; ?>

setTimeout(()=>location.reload(),30000);
</script>

<?php
$current = basename($_SERVER['PHP_SELF']);

// Check for any overtime PCs from DB
if (!isset($pdo)) include __DIR__ . '/../config/db.php';
$overtimeCount = 0;
$overtimePCs = [];
try {
    $stmt = $pdo->query("
        SELECT p.name, s.start_time, s.time_limit
        FROM sessions s
        JOIN pcs p ON p.id = s.pc_id
        WHERE s.end_time IS NULL AND s.time_limit IS NOT NULL
    ");
    foreach ($stmt->fetchAll() as $row) {
        $elapsed = (time() - strtotime($row['start_time'])) / 60;
        if ($elapsed > $row['time_limit']) {
            $overtimeCount++;
            $overtimePCs[] = htmlspecialchars($row['name']);
        }
    }
} catch (Exception $e) {}
?>

<?php if ($overtimeCount > 0): ?>
<div class="global-overtime-bar" id="globalOvertimeBar">
    <span>⚠</span>
    OVERTIME ALERT — <?= implode(', ', $overtimePCs) ?> <?= $overtimeCount === 1 ? 'has' : 'have' ?> exceeded their time limit!
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
// Clock
function updateTime() {
    const now = new Date();
    let h = now.getHours(), m = now.getMinutes(), s = now.getSeconds();
    const ampm = h >= 12 ? 'PM' : 'AM';
    h = h % 12 || 12;
    document.getElementById('navTime').textContent =
        String(h).padStart(2,'0') + ':' + String(m).padStart(2,'0') + ':' + String(s).padStart(2,'0') + ' ' + ampm;
}
updateTime(); setInterval(updateTime, 1000);

// Overtime alarm sound on ALL pages
const hasOvertime = <?= $overtimeCount > 0 ? 'true' : 'false' ?>;
let alarmPlaying = false;

function playBeep() {
    try {
        const ctx = new (window.AudioContext || window.webkitAudioContext)();
        const o = ctx.createOscillator();
        const g = ctx.createGain();
        o.connect(g); g.connect(ctx.destination);
        o.type = 'square';
        o.frequency.value = 880;
        g.gain.setValueAtTime(0.3, ctx.currentTime);
        g.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.4);
        o.start(); o.stop(ctx.currentTime + 0.4);
        setTimeout(playBeep, 3000);
    } catch(e) {}
}

if (hasOvertime) {
    // Start alarm on first user interaction (browser policy)
    function startAlarm() {
        if (!alarmPlaying) {
            alarmPlaying = true;
            playBeep();
        }
        document.removeEventListener('click', startAlarm);
        document.removeEventListener('keydown', startAlarm);
    }
    document.addEventListener('click', startAlarm);
    document.addEventListener('keydown', startAlarm);
    // Also try immediately after short delay
    setTimeout(() => { if (!alarmPlaying) startAlarm(); }, 1000);
}

// Auto-refresh every 30s to update overtime status on all pages
setTimeout(() => location.reload(), 30000);
</script>

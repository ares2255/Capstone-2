<?php
$current = basename($_SERVER['PHP_SELF']);

// Check for any overtime PCs
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
            $overtimePCs[] = $row['name'];
        }
    }
} catch (Exception $e) {}
?>

<?php if ($overtimeCount > 0): ?>
<div class="global-overtime-bar">
    <span>⚠</span>
    OVERTIME ALERT —
    <?= implode(', ', array_map('htmlspecialchars', $overtimePCs)) ?>
    <?= $overtimeCount === 1 ? 'has' : 'have' ?> exceeded their time limit!
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
        <?php if ($overtimeCount > 0): ?>
        <span class="nav-overtime-badge">
            <i class="fas fa-exclamation-triangle"></i> <?= $overtimeCount ?> OVERTIME
        </span>
        <?php endif; ?>
        <span class="nav-time" id="navTime"></span>
        <span class="nav-user"><i class="fas fa-user"></i> <?= htmlspecialchars($display_user ?? '') ?></span>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
</nav>

<script>
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
setTimeout(() => location.reload(), 30000);
</script>

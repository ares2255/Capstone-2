<?php
$current = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar">
    <div class="nav-brand">
        <i class="fas fa-clock"></i>
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
</script>

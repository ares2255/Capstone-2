<?php
// Get the current filename to highlight the active link
$current_page = basename($_SERVER['PHP_SELF']);
?>
<style>
    .bottom-nav {
        position: fixed;
        bottom: 25px;
        left: 50%;
        transform: translateX(-50%);
        background: #071426;
        padding: 12px 40px;
        border-radius: 50px;
        display: flex;
        gap: 40px;
        border: 1px solid #132742;
        box-shadow: 0 15px 35px rgba(0,0,0,0.6);
        z-index: 9999;
        backdrop-filter: blur(10px);
    }

    .nav-item {
        color: #9fb3d9;
        text-decoration: none;
        font-size: 13px;
        font-weight: 600;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 5px;
        transition: all 0.3s ease;
        opacity: 0.6;
    }

    .nav-item i {
        font-size: 20px;
        margin-bottom: 2px;
    }

    .nav-item:hover {
        opacity: 1;
        color: #1fb6ff;
    }

    .nav-item.active {
        opacity: 1;
        color: #1fb6ff;
        transform: translateY(-5px);
    }

    /* Small glow effect for active icon */
    .nav-item.active i {
        text-shadow: 0 0 10px rgba(31, 182, 255, 0.5);
    }

    /* Hide the top navbar on pages if you prefer only the bottom one */
    /* .navbar { display: none; } */
</style>

<div class="bottom-nav">
    <a href="counter.php" class="nav-item <?php echo ($current_page == 'counter.php') ? 'active' : ''; ?>">
        <i>🖥</i>
        <span>Counter</span>
    </a>
    <a href="printing.php" class="nav-item <?php echo ($current_page == 'printing.php') ? 'active' : ''; ?>">
        <i>🖨</i>
        <span>Printing</span>
    </a>
    <a href="dashboard.php" class="nav-item <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
        <i>📊</i>
        <span>Stats</span>
    </a>
    <a href="settings.php" class="nav-item <?php echo ($current_page == 'settings.php') ? 'active' : ''; ?>">
        <i>⚙</i>
        <span>Settings</span>
    </a>
</div>
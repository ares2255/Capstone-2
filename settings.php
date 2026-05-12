<?php
session_start();
error_reporting(0);
ini_set('display_errors', 0);
include "config/db.php";

if (!isset($_SESSION['admin_username']) && !isset($_SESSION['username'])) {
    header("Location: index.php"); exit();
}

$is_admin     = isset($_SESSION['admin_username']);
$display_user = $is_admin ? $_SESSION['admin_username'] : $_SESSION['username'];

$rates       = $pdo->query("SELECT * FROM settings WHERE id=1")->fetch();
$existingPCs = $pdo->query("SELECT name FROM pcs")->fetchAll(PDO::FETCH_COLUMN);
$allPCs      = $pdo->query("SELECT id, name FROM pcs ORDER BY name ASC")->fetchAll();
$packages    = $pdo->query("SELECT * FROM packages ORDER BY minutes ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>The Desktop | Settings</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="includes/navbar.css">
<link rel="stylesheet" href="includes/theme.css">
<script>(function(){if(localStorage.getItem('settings_theme')==='light')document.documentElement.classList.add('light-mode');})()</script>
<style>
html{overflow-y:scroll;}

/* ── DARK MODE (default) ── */
body{background:linear-gradient(135deg,#0d1117 0%,#1a1a2e 50%,#16213e 100%);color:white;font-family:'Inter',sans-serif;margin:0;min-height:100vh;transition:background .3s,color .3s;}
.main-content{padding:36px 40px;display:flex;flex-direction:column;align-items:center;gap:20px;}
.cards-wrapper{display:flex;gap:20px;justify-content:center;flex-wrap:wrap;width:100%;max-width:1300px;}
.card{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);border-radius:16px;padding:26px;flex:1;min-width:320px;transition:background .3s,border-color .3s;}
.card h3{color:#7b9cff;margin:0 0 18px;font-size:15px;display:flex;align-items:center;gap:8px;border-bottom:1px solid rgba(255,255,255,.08);padding-bottom:12px;transition:color .3s,border-color .3s;}
.input-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
.input-group label{display:block;font-size:11px;color:#8aa0c5;margin-bottom:5px;text-transform:uppercase;letter-spacing:.5px;transition:color .3s;}
.input-group input{width:100%;padding:10px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);color:white;border-radius:8px;box-sizing:border-box;font-family:'Inter',sans-serif;transition:.3s;}
.input-group input:focus{outline:none;border-color:#1e2a78;box-shadow:0 0 0 3px rgba(30,42,120,.2);}
.section-label{grid-column:1/-1;font-size:11px;color:#7b9cff;text-transform:uppercase;letter-spacing:1px;padding-top:8px;border-top:1px solid rgba(255,255,255,.08);margin-top:4px;}
.btn-save{width:100%;background:#1e2a78;color:white;border:none;padding:12px;border-radius:10px;margin-top:18px;cursor:pointer;font-weight:bold;transition:.2s;}
.btn-save:hover{background:#2d3eaa;transform:translateY(-1px);}
select{width:100%;padding:10px;background:#0f1623;border:1px solid rgba(255,255,255,.15);color:white;border-radius:8px;box-sizing:border-box;font-family:'Inter',sans-serif;transition:.3s;}
select:focus{outline:none;border-color:#1e2a78;box-shadow:0 0 0 3px rgba(30,42,120,.2);}
select option{background:#0f1623;color:white;}
select option:hover,select option:checked{background:#1e2a78;color:white;}
select option:disabled{color:#4a5f7a;}
.btn-add{background:#1e2a78;color:white;border:none;padding:0 16px;border-radius:8px;cursor:pointer;font-weight:bold;white-space:nowrap;height:40px;transition:.2s;}
.btn-add:hover{background:#2d3eaa;}
.btn-del{background:#ff4d4d;color:white;border:none;padding:0 18px;border-radius:8px;cursor:pointer;font-weight:bold;white-space:nowrap;transition:.2s;}
.btn-del:hover{background:#e03030;}
.btn-clear{width:100%;background:transparent;border:1px solid #ff4d4d;color:#ff4d4d;padding:10px;border-radius:8px;cursor:pointer;margin-top:8px;transition:.2s;}
.btn-clear:hover{background:rgba(255,77,77,.1);}
.alert-success{background:rgba(46,204,113,.1);color:#2ecc71;border:1px solid rgba(46,204,113,.3);padding:12px;border-radius:10px;text-align:center;width:100%;max-width:1300px;}
.modal-overlay{display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.8);backdrop-filter:blur(10px);z-index:9999;align-items:center;justify-content:center;}
.modal-card{background:#ffffff;border-radius:24px;padding:36px;width:400px;text-align:center;border-bottom:8px solid #ff4d4d;box-shadow:0 20px 60px rgba(0,0,0,.5);color:#1e293b;}
.modal-card i{color:#ff4d4d;font-size:48px;display:block;margin-bottom:16px;}
.modal-card h3{color:#1e293b;margin:0 0 10px;font-size:20px;}
.modal-card p{color:#64748b;font-size:14px;margin-bottom:28px;}
.modal-actions{display:flex;gap:12px;}
.btn-m-cancel{flex:1;background:#f1f5f9;color:#64748b;border:1px solid #e2e8f0;padding:12px;border-radius:8px;cursor:pointer;font-weight:bold;transition:.2s;}
.btn-m-cancel:hover{background:#e2e8f0;}
.btn-m-confirm{flex:1;background:#ff4d4d;color:white;border:none;padding:12px;border-radius:8px;cursor:pointer;font-weight:bold;transition:.2s;}
.btn-m-confirm:hover{background:#e03030;}

/* Package Manager */
.pkg-add-row{display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:10px;align-items:end;margin-bottom:20px;padding-bottom:20px;border-bottom:1px solid rgba(255,255,255,.08);}
.pkg-add-row label{display:block;font-size:11px;color:#8aa0c5;margin-bottom:5px;text-transform:uppercase;letter-spacing:.5px;}
.pkg-add-row input{width:100%;padding:10px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);color:white;border-radius:8px;box-sizing:border-box;}
.pkg-add-row input:focus{outline:none;border-color:#1e2a78;}
.pkg-table{width:100%;border-collapse:collapse;}
.pkg-table th{font-size:11px;color:#8aa0c5;text-transform:uppercase;letter-spacing:.5px;padding:8px 12px;border-bottom:1px solid rgba(255,255,255,.08);text-align:left;}
.pkg-table td{padding:11px 12px;border-bottom:1px solid rgba(255,255,255,.05);font-size:14px;}
.pkg-table tr:last-child td{border-bottom:none;}
.pkg-badge{display:inline-block;background:rgba(74,108,247,.15);border:1px solid rgba(74,108,247,.3);color:#7b9cff;padding:3px 12px;border-radius:20px;font-size:12px;font-weight:700;letter-spacing:.5px;}
.pkg-price{color:#2ecc71;font-weight:700;}
.pkg-mins{color:#8aa0c5;font-size:12px;}
.btn-pkg-del{background:rgba(255,77,77,.1);border:1px solid rgba(255,77,77,.3);color:#ff4d4d;padding:5px 12px;border-radius:6px;cursor:pointer;font-size:12px;transition:.2s;}
.btn-pkg-del:hover{background:rgba(255,77,77,.25);}
.pkg-empty{text-align:center;color:#8aa0c5;padding:28px 0;font-size:13px;}


/* ── Toggle Button (Settings only) ── */
.theme-toggle{position:fixed;bottom:28px;right:28px;width:52px;height:52px;border-radius:50%;background:#1e2a78;color:white;border:none;cursor:pointer;font-size:20px;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 18px rgba(30,42,120,.45);z-index:9000;transition:background .3s,transform .2s,box-shadow .3s;}
.theme-toggle:hover{background:#2d3eaa;transform:scale(1.1);}
</style>
</head>
<body>
<?php include 'includes/navbar.php'; ?>
<div class="main-content">
    <?php if(isset($_GET['status']) && $_GET['status']==='success'): ?>
        <div class="alert-success"><i class="fas fa-check-circle"></i> Settings saved successfully!</div>
    <?php endif; ?>

    <div class="cards-wrapper">

        <!-- ── Time Packages ── -->
        <div class="card" style="min-width:480px;flex:2;">
            <h3><i class="fas fa-clock"></i> Time Packages
                <span style="margin-left:auto;font-size:11px;color:#4a5f7a;font-weight:400;"><?= count($packages) ?> package(s) active</span>
            </h3>

            <!-- Add form -->
            <form action="save_package.php" method="POST">
                <input type="hidden" name="action" value="add">
                <div class="pkg-add-row">
                    <div>
                        <label>Hours</label>
                        <input type="number" name="hours" min="0" max="23" value="1" placeholder="0">
                    </div>
                    <div>
                        <label>Minutes</label>
                        <input type="number" name="mins" min="0" max="59" value="0" placeholder="0">
                    </div>
                    <div>
                        <label>Price (&#8369;)</label>
                        <input type="number" name="price" step="0.01" min="0" placeholder="0.00">
                    </div>
                    <button type="submit" class="btn-add"><i class="fas fa-plus"></i> Add</button>
                </div>
            </form>

            <!-- Package list -->
            <?php if(count($packages) > 0): ?>
            <table class="pkg-table">
                <thead>
                    <tr>
                        <th>Package</th>
                        <th>Total Minutes</th>
                        <th>Price</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach($packages as $pkg):
                    $h = intdiv($pkg['minutes'], 60);
                    $m = $pkg['minutes'] % 60;
                    if($h > 0 && $m > 0)      $label = "{$h}HR {$m}MIN";
                    elseif($h > 0)             $label = $h == 1 ? "1 HR" : "{$h} HRS";
                    else                       $label = "{$m} MIN";
                ?>
                <tr>
                    <td><span class="pkg-badge"><?= htmlspecialchars($label) ?></span></td>
                    <td class="pkg-mins"><?= $pkg['minutes'] ?> min</td>
                    <td class="pkg-price">&#8369;<?= number_format($pkg['price'], 2) ?></td>
                    <td>
                        <form action="save_package.php" method="POST" style="margin:0;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $pkg['id'] ?>">
                            <button type="submit" class="btn-pkg-del"><i class="fas fa-trash"></i> Remove</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
                <div class="pkg-empty">
                    <i class="fas fa-box-open" style="font-size:28px;display:block;margin-bottom:10px;color:#38bdf8;opacity:.4;"></i>
                    No packages yet. Add your first package above.
                </div>
            <?php endif; ?>
        </div>

        <div style="display:flex;flex-direction:column;gap:20px;flex:1;min-width:280px;">
            <!-- ── Other Rates ── -->
            <div class="card">
                <h3><i class="fas fa-tag"></i> Other Rates</h3>
                <form action="save_all_rates.php" method="POST">
                    <div class="input-grid">
                        <div class="input-group"><label>Min Charge (&#8369;)</label><input type="number" step="0.01" name="min_charge" value="<?= $rates['minimum_charge'] ?? '' ?>"></div>
                        <div class="input-group"><label>B&amp;W Print (&#8369;)</label><input type="number" step="0.01" name="bw_rate" value="<?= $rates['bw_rate'] ?? '' ?>"></div>
                        <div class="input-group" style="grid-column:1/-1;"><label>Color Print (&#8369;)</label><input type="number" step="0.01" name="color_rate" value="<?= $rates['color_rate'] ?? '' ?>"></div>
                    </div>
                    <button type="submit" class="btn-save"><i class="fas fa-save"></i> Save Rates</button>
                </form>
            </div>

            <!-- ── PC Management ── -->
            <div class="card">
                <h3><i class="fas fa-desktop"></i> PC Management</h3>
                <form action="add_specific_pc.php" method="POST">
                    <label style="font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:.5px;">Add New Unit</label>
                    <div style="display:flex;gap:10px;margin-bottom:18px;margin-top:6px;">
                        <select name="pc_number" required>
                            <option value="" disabled selected>Select PC Number</option>
                            <?php for($i=1;$i<=50;$i++){
                                $name="PC-".str_pad($i,2,'0',STR_PAD_LEFT);
                                if(!in_array($name,$existingPCs)) echo "<option value='$name'>$name</option>";
                            } ?>
                        </select>
                        <button type="submit" name="add_pc" class="btn-add">Add</button>
                    </div>
                </form>
                <form action="add_specific_pc.php" method="POST">
                    <label style="font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:.5px;">Remove Specific Unit</label>
                    <div style="display:flex;gap:10px;margin-bottom:18px;margin-top:6px;">
                        <select name="pc_id" required>
                            <option value="" disabled selected>Select PC</option>
                            <?php foreach($allPCs as $pc) echo "<option value='{$pc['id']}'>{$pc['name']}</option>"; ?>
                        </select>
                        <button type="submit" name="delete_pc" class="btn-del">Delete</button>
                    </div>
                </form>
                <form id="clearAllForm" action="add_specific_pc.php" method="POST">
                    <input type="hidden" name="clear_all" value="1">
                    <button type="button" class="btn-clear" onclick="showClearModal()"><i class="fas fa-trash-alt"></i> Clear All PC Units</button>
                </form>
            </div>
        </div>

    </div>
</div>

<div id="clearUnitsModal" class="modal-overlay">
    <div class="modal-card">
        <i class="fas fa-exclamation-triangle"></i>
        <h3>Clear All Units?</h3>
        <p>This will permanently remove all PC units. This cannot be undone.</p>
        <div class="modal-actions">
            <button class="btn-m-cancel" onclick="closeClearModal()">Cancel</button>
            <button class="btn-m-confirm" onclick="submitClearAll()">Yes, Clear All</button>
        </div>
    </div>
</div>
<!-- ── Theme Toggle Button (Settings only) ── -->
<button class="theme-toggle" id="themeToggle" title="Toggle Light/Dark Mode">
    <i class="fas fa-sun" id="themeIcon"></i>
</button>

<script>
function showClearModal(){document.getElementById('clearUnitsModal').style.display='flex';}
function closeClearModal(){document.getElementById('clearUnitsModal').style.display='none';}
function submitClearAll(){document.getElementById('clearAllForm').submit();}
window.onclick=e=>{if(e.target==document.getElementById('clearUnitsModal'))closeClearModal();}

// ── Light/Dark Mode ──
const toggleBtn = document.getElementById('themeToggle');
const themeIcon = document.getElementById('themeIcon');

function applyTheme(mode){
    if(mode === 'light'){
        document.documentElement.classList.add('light-mode');
        themeIcon.classList.replace('fa-sun','fa-moon');
        toggleBtn.title = 'Switch to Dark Mode';
    } else {
        document.documentElement.classList.remove('light-mode');
        themeIcon.classList.replace('fa-moon','fa-sun');
        toggleBtn.title = 'Switch to Light Mode';
    }
}

const savedTheme = localStorage.getItem('settings_theme') || 'dark';
applyTheme(savedTheme);

toggleBtn.addEventListener('click', () => {
    const isLight = document.documentElement.classList.contains('light-mode');
    const newTheme = isLight ? 'dark' : 'light';
    localStorage.setItem('settings_theme', newTheme);
    applyTheme(newTheme);
});
</script>
</body></html>

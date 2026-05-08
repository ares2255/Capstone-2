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
<link rel="stylesheet" href="includes/navbar.css">
<style>
html{overflow-y:scroll;}
body{background-color:#050b14;background-image:linear-gradient(rgba(19,39,66,.3) 1px,transparent 1px),linear-gradient(90deg,rgba(19,39,66,.3) 1px,transparent 1px);background-size:50px 50px;color:white;font-family:'Segoe UI',sans-serif;margin:0;min-height:100vh;}
.main-content{padding:36px 40px;display:flex;flex-direction:column;align-items:center;gap:20px;}
.cards-wrapper{display:flex;gap:20px;justify-content:center;flex-wrap:wrap;width:100%;max-width:1300px;}
.card{background:rgba(10,25,47,.85);border:1px solid #132742;border-radius:12px;padding:26px;flex:1;min-width:320px;}
.card h3{color:#38bdf8;margin:0 0 18px;font-size:15px;display:flex;align-items:center;gap:8px;border-bottom:1px solid #132742;padding-bottom:12px;}
.input-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
.input-group label{display:block;font-size:11px;color:#64748b;margin-bottom:5px;text-transform:uppercase;letter-spacing:.5px;}
.input-group input{width:100%;padding:10px;background:#050814;border:1px solid #132742;color:white;border-radius:6px;box-sizing:border-box;}
.input-group input:focus{outline:none;border-color:#38bdf8;}
.section-label{grid-column:1/-1;font-size:11px;color:#38bdf8;text-transform:uppercase;letter-spacing:1px;padding-top:8px;border-top:1px solid #132742;margin-top:4px;}
.btn-save{width:100%;background:#2ecc71;color:white;border:none;padding:12px;border-radius:8px;margin-top:18px;cursor:pointer;font-weight:bold;transition:.2s;}
.btn-save:hover{background:#27ae60;}
select{width:100%;padding:10px;background:#050814;border:1px solid #132742;color:white;border-radius:6px;box-sizing:border-box;}
select:focus{outline:none;border-color:#38bdf8;}
.btn-add{background:#38bdf8;color:#000;border:none;padding:0 16px;border-radius:6px;cursor:pointer;font-weight:bold;white-space:nowrap;height:40px;}
.btn-del{background:#ff4d4d;color:white;border:none;padding:0 18px;border-radius:6px;cursor:pointer;font-weight:bold;white-space:nowrap;}
.btn-clear{width:100%;background:transparent;border:1px solid #ff4d4d;color:#ff4d4d;padding:10px;border-radius:8px;cursor:pointer;margin-top:8px;transition:.2s;}
.btn-clear:hover{background:rgba(255,77,77,.1);}
.alert-success{background:rgba(46,204,113,.1);color:#2ecc71;border:1px solid #2ecc71;padding:12px;border-radius:8px;text-align:center;width:100%;max-width:1300px;}
.modal-overlay{display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.85);backdrop-filter:blur(6px);z-index:9999;align-items:center;justify-content:center;}
.modal-card{background:#0a192f;border:1px solid #ff4d4d;box-shadow:0 0 30px rgba(255,77,77,.2);border-radius:14px;padding:30px;width:400px;text-align:center;}
.modal-card i{color:#ff4d4d;font-size:48px;display:block;margin-bottom:16px;}
.modal-card h3{color:white;margin:0 0 10px;font-size:20px;}
.modal-card p{color:#8aa0c5;font-size:14px;margin-bottom:28px;}
.modal-actions{display:flex;gap:12px;}
.btn-m-cancel{flex:1;background:#1e293b;color:white;border:none;padding:12px;border-radius:8px;cursor:pointer;font-weight:bold;}
.btn-m-confirm{flex:1;background:#ff4d4d;color:white;border:none;padding:12px;border-radius:8px;cursor:pointer;font-weight:bold;}

/* Package Manager */
.pkg-add-row{display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:10px;align-items:end;margin-bottom:20px;padding-bottom:20px;border-bottom:1px solid #132742;}
.pkg-add-row label{display:block;font-size:11px;color:#64748b;margin-bottom:5px;text-transform:uppercase;letter-spacing:.5px;}
.pkg-add-row input{width:100%;padding:10px;background:#050814;border:1px solid #132742;color:white;border-radius:6px;box-sizing:border-box;}
.pkg-add-row input:focus{outline:none;border-color:#38bdf8;}
.pkg-table{width:100%;border-collapse:collapse;}
.pkg-table th{font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:.5px;padding:8px 12px;border-bottom:1px solid #132742;text-align:left;}
.pkg-table td{padding:11px 12px;border-bottom:1px solid rgba(19,39,66,.6);font-size:14px;}
.pkg-table tr:last-child td{border-bottom:none;}
.pkg-badge{display:inline-block;background:rgba(56,189,248,.1);border:1px solid rgba(56,189,248,.3);color:#38bdf8;padding:3px 12px;border-radius:20px;font-size:12px;font-weight:700;letter-spacing:.5px;}
.pkg-price{color:#2ecc71;font-weight:700;}
.pkg-mins{color:#64748b;font-size:12px;}
.btn-pkg-del{background:rgba(255,77,77,.1);border:1px solid rgba(255,77,77,.3);color:#ff4d4d;padding:5px 12px;border-radius:6px;cursor:pointer;font-size:12px;transition:.2s;}
.btn-pkg-del:hover{background:rgba(255,77,77,.25);}
.pkg-empty{text-align:center;color:#4a5f7a;padding:28px 0;font-size:13px;}
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
<script>
function showClearModal(){document.getElementById('clearUnitsModal').style.display='flex';}
function closeClearModal(){document.getElementById('clearUnitsModal').style.display='none';}
function submitClearAll(){document.getElementById('clearAllForm').submit();}
window.onclick=e=>{if(e.target==document.getElementById('clearUnitsModal'))closeClearModal();}
</script>
</body></html>

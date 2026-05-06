<?php
session_start();
include "config/db.php";
if (!isset($_SESSION['admin_username']) && !isset($_SESSION['username'])) {
    header("Location: index.php"); exit();
}
$is_admin = isset($_SESSION['admin_username']);
$display_user = $is_admin ? $_SESSION['admin_username'] : $_SESSION['username'];

try {
    $categories = $pdo->query("SELECT DISTINCT category FROM products ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);
    $products = $pdo->query("SELECT * FROM products WHERE active=true ORDER BY category, name")->fetchAll();
} catch(PDOException $e) { $products = []; $categories = []; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>The Desktop | POS</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="includes/navbar.css">
<style>
html{overflow-y:scroll;scrollbar-gutter:stable;}
body{background-color:#050b14;background-image:linear-gradient(rgba(19,39,66,.3) 1px,transparent 1px),linear-gradient(90deg,rgba(19,39,66,.3) 1px,transparent 1px);background-size:50px 50px;color:white;font-family:'Segoe UI',sans-serif;margin:0;min-height:100vh;}
.pos-layout{display:grid;grid-template-columns:1fr 380px;gap:0;height:calc(100vh - 60px);}
.products-panel{padding:24px;overflow-y:auto;}
.cart-panel{background:rgba(10,25,47,.95);border-left:1px solid #132742;display:flex;flex-direction:column;height:100%;}
.panel-header{font-size:15px;font-weight:700;color:#38bdf8;margin-bottom:16px;display:flex;align-items:center;gap:8px;}
.search-bar{display:flex;gap:10px;margin-bottom:16px;}
.search-bar input{flex:1;background:#0a192f;border:1px solid #132742;color:white;padding:10px 14px;border-radius:8px;font-size:14px;outline:none;}
.search-bar input::placeholder{color:#4a5f7a;}
.cat-tabs{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:20px;}
.cat-tab{background:#0a192f;border:1px solid #132742;color:#8aa0c5;padding:6px 14px;border-radius:20px;font-size:12px;cursor:pointer;transition:.2s;}
.cat-tab.active,.cat-tab:hover{background:#38bdf8;color:#000;border-color:#38bdf8;}
.products-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:14px;}
.product-card{background:rgba(10,25,47,.85);border:1px solid #132742;border-radius:12px;padding:16px;cursor:pointer;transition:.2s;text-align:center;position:relative;}
.product-card:hover{border-color:#38bdf8;transform:translateY(-2px);}
.product-card .emoji{font-size:36px;margin-bottom:8px;display:block;}
.product-card .pname{font-size:13px;font-weight:600;margin-bottom:4px;}
.product-card .pprice{color:#2ecc71;font-size:14px;font-weight:700;}
.product-card .pstock{font-size:11px;color:#8aa0c5;margin-top:4px;}
.product-card.out-of-stock{opacity:.5;cursor:not-allowed;}
.add-btn{position:absolute;top:8px;right:8px;background:#38bdf8;color:#000;border:none;width:24px;height:24px;border-radius:50%;font-size:14px;cursor:pointer;display:flex;align-items:center;justify-content:center;font-weight:bold;}
/* Cart */
.cart-header{padding:20px 20px 0;border-bottom:1px solid #132742;padding-bottom:16px;}
.cart-header h3{margin:0;font-size:15px;color:#38bdf8;display:flex;align-items:center;gap:8px;}
.cart-items{flex:1;overflow-y:auto;padding:12px 20px;}
.cart-item{display:flex;align-items:center;gap:10px;padding:12px 0;border-bottom:1px solid #0d2137;}
.cart-item .ci-name{flex:1;font-size:13px;}
.cart-item .ci-price{color:#2ecc71;font-size:13px;font-weight:600;white-space:nowrap;}
.qty-ctrl{display:flex;align-items:center;gap:6px;}
.qty-btn{background:#132742;border:none;color:white;width:24px;height:24px;border-radius:6px;cursor:pointer;font-size:14px;}
.qty-num{font-size:13px;min-width:20px;text-align:center;}
.remove-btn{color:#ff4d4d;cursor:pointer;font-size:14px;padding:4px;}
.cart-empty{text-align:center;padding:40px 20px;color:#4a5f7a;}
.cart-empty i{font-size:40px;display:block;margin-bottom:12px;}
.cart-footer{padding:16px 20px;border-top:1px solid #132742;}
.total-line{display:flex;justify-content:space-between;margin-bottom:8px;font-size:13px;color:#8aa0c5;}
.total-line.grand{font-size:20px;font-weight:700;color:white;margin-top:12px;}
.total-line.grand span:last-child{color:#2ecc71;}
.cash-input{margin:14px 0;position:relative;}
.cash-input label{font-size:12px;color:#8aa0c5;text-transform:uppercase;letter-spacing:1px;display:block;margin-bottom:6px;}
.cash-input input{width:100%;background:#0a192f;border:1px solid #132742;color:white;padding:10px 14px;border-radius:8px;font-size:16px;outline:none;box-sizing:border-box;}
.change-display{background:rgba(46,204,113,.08);border:1px solid rgba(46,204,113,.2);border-radius:8px;padding:12px;margin-bottom:14px;display:flex;justify-content:space-between;align-items:center;}
.change-display span:first-child{color:#8aa0c5;font-size:13px;}
.change-display span:last-child{color:#2ecc71;font-size:18px;font-weight:700;}
.checkout-btn{width:100%;background:#2ecc71;color:#000;border:none;padding:14px;border-radius:10px;font-size:15px;font-weight:700;cursor:pointer;transition:.2s;}
.checkout-btn:hover{background:#27ae60;}
.checkout-btn:disabled{background:#132742;color:#4a5f7a;cursor:not-allowed;}
.clear-btn{width:100%;background:transparent;border:1px solid #ff4d4d;color:#ff4d4d;padding:10px;border-radius:8px;font-size:13px;cursor:pointer;margin-top:8px;}
/* Manage Products Modal */
.modal-bg{position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.85);backdrop-filter:blur(5px);z-index:2000;display:none;align-items:center;justify-content:center;}
.modal-bg.show{display:flex;}
.modal-box{background:#0f172a;border:1px solid #1e293b;border-radius:16px;width:700px;max-width:95vw;max-height:90vh;display:flex;flex-direction:column;}
.modal-head{padding:20px 24px;border-bottom:1px solid #1e293b;display:flex;align-items:center;justify-content:space-between;}
.modal-head h3{margin:0;font-size:16px;}
.modal-close{background:none;border:none;color:#8aa0c5;font-size:20px;cursor:pointer;}
.modal-body{padding:24px;overflow-y:auto;flex:1;}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:20px;}
.form-group{display:flex;flex-direction:column;gap:6px;}
.form-group label{font-size:12px;color:#8aa0c5;text-transform:uppercase;letter-spacing:1px;}
.form-group input,.form-group select{background:#0a192f;border:1px solid #132742;color:white;padding:10px 12px;border-radius:8px;font-size:14px;outline:none;}
.form-group input:focus,.form-group select:focus{border-color:#38bdf8;}
.form-group select option{background:#0a192f;}
.add-product-btn{background:#38bdf8;color:#000;border:none;padding:10px 20px;border-radius:8px;font-weight:700;cursor:pointer;width:100%;}
.prod-table{width:100%;border-collapse:collapse;font-size:13px;margin-top:20px;}
.prod-table th{text-align:left;color:#8aa0c5;border-bottom:1px solid #132742;padding:10px 8px;font-size:11px;text-transform:uppercase;letter-spacing:1px;}
.prod-table td{padding:12px 8px;border-bottom:1px solid #0d2137;vertical-align:middle;}
.del-prod{color:#ff4d4d;cursor:pointer;background:none;border:none;font-size:14px;}
.toggle-btn{background:none;border:1px solid #132742;color:#8aa0c5;padding:4px 10px;border-radius:6px;font-size:11px;cursor:pointer;}
.toggle-btn.on{border-color:#2ecc71;color:#2ecc71;}
/* Receipt Modal */
.receipt{background:#fff;color:#000;padding:24px;border-radius:8px;max-width:320px;font-family:monospace;}
.receipt h2{text-align:center;margin:0 0 4px;font-size:16px;}
.receipt p{text-align:center;margin:0 0 12px;font-size:11px;color:#666;}
.receipt hr{border:1px dashed #ccc;margin:10px 0;}
.receipt-row{display:flex;justify-content:space-between;font-size:13px;margin:4px 0;}
.receipt-total{font-weight:bold;font-size:15px;margin-top:8px;}
.receipt-footer{text-align:center;margin-top:12px;font-size:11px;color:#666;}
.print-btn{background:#0a192f;color:white;border:none;padding:10px 20px;border-radius:8px;cursor:pointer;margin-top:12px;width:100%;}
</style>
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div class="pos-layout">
    <!-- Products Panel -->
    <div class="products-panel">
        <div class="panel-header"><i class="fas fa-store"></i> Food & Drinks</div>

        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
            <div class="search-bar" style="margin:0;flex:1;margin-right:12px;">
                <input type="text" id="searchInput" placeholder="Search products..." onkeyup="filterProducts()">
            </div>
            <?php if($is_admin): ?>
            <button onclick="document.getElementById('manageModal').classList.add('show')" style="background:#a855f7;color:white;border:none;padding:10px 16px;border-radius:8px;cursor:pointer;font-size:13px;white-space:nowrap;font-weight:600;">
                <i class="fas fa-boxes"></i> Manage Products
            </button>
            <?php endif; ?>
        </div>

        <div class="cat-tabs">
            <div class="cat-tab active" onclick="filterCat('all',this)">All</div>
            <?php foreach($categories as $cat): ?>
            <div class="cat-tab" onclick="filterCat('<?= htmlspecialchars($cat) ?>',this)"><?= htmlspecialchars($cat) ?></div>
            <?php endforeach; ?>
        </div>

        <div class="products-grid" id="productsGrid">
            <?php if(empty($products)): ?>
            <div style="grid-column:1/-1;text-align:center;padding:60px 20px;color:#4a5f7a;">
                <i class="fas fa-box-open" style="font-size:40px;display:block;margin-bottom:12px;"></i>
                No products yet. <?= $is_admin ? 'Click "Manage Products" to add some!' : 'Ask admin to add products.' ?>
            </div>
            <?php else: ?>
            <?php foreach($products as $p): ?>
            <div class="product-card <?= $p['stock']==0?'out-of-stock':'' ?>"
                 data-cat="<?= htmlspecialchars($p['category']) ?>"
                 data-name="<?= htmlspecialchars($p['name']) ?>"
                 onclick="<?= $p['stock']!=0?"addToCart({$p['id']},".json_encode($p['name']).",{$p['price']},".json_encode($p['emoji']).")":'void(0)' ?>">
                <span class="emoji"><?= $p['emoji'] ?></span>
                <div class="pname"><?= htmlspecialchars($p['name']) ?></div>
                <div class="pprice">₱<?= number_format($p['price'],2) ?></div>
                <div class="pstock"><?= $p['stock']>0?"Stock: {$p['stock']}":'Out of stock' ?></div>
                <?php if($p['stock']!=0): ?><button class="add-btn">+</button><?php endif; ?>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Cart Panel -->
    <div class="cart-panel">
        <div class="cart-header">
            <h3><i class="fas fa-shopping-cart"></i> Order <span id="cartCount" style="background:#38bdf8;color:#000;font-size:11px;padding:2px 8px;border-radius:20px;margin-left:4px;">0</span></h3>
        </div>
        <div class="cart-items" id="cartItems">
            <div class="cart-empty"><i class="fas fa-shopping-basket"></i>Cart is empty<br><small style="font-size:12px;">Click a product to add</small></div>
        </div>
        <div class="cart-footer">
            <div class="total-line"><span>Subtotal</span><span id="subtotal">₱0.00</span></div>
            <div class="total-line grand"><span>TOTAL</span><span id="grandTotal">₱0.00</span></div>
            <div class="cash-input">
                <label>Cash Received</label>
                <input type="number" id="cashInput" placeholder="0.00" min="0" step="0.01" oninput="calcChange()">
            </div>
            <div class="change-display">
                <span>Change</span>
                <span id="changeDisplay">₱0.00</span>
            </div>
            <button class="checkout-btn" id="checkoutBtn" onclick="checkout()" disabled>
                <i class="fas fa-check-circle"></i> Checkout
            </button>
            <button class="clear-btn" onclick="clearCart()"><i class="fas fa-trash"></i> Clear Cart</button>
        </div>
    </div>
</div>

<!-- Manage Products Modal -->
<div class="modal-bg" id="manageModal">
    <div class="modal-box">
        <div class="modal-head">
            <h3><i class="fas fa-boxes" style="color:#a855f7;margin-right:8px;"></i> Manage Products</h3>
            <button class="modal-close" onclick="document.getElementById('manageModal').classList.remove('show')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <div class="form-grid">
                <div class="form-group">
                    <label>Product Name</label>
                    <input type="text" id="newName" placeholder="e.g. Iced Coffee">
                </div>
                <div class="form-group">
                    <label>Price (₱)</label>
                    <input type="number" id="newPrice" placeholder="0.00" min="0" step="0.01">
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <input type="text" id="newCategory" placeholder="e.g. Drinks, Snacks, Food" list="catList">
                    <datalist id="catList">
                        <?php foreach($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat) ?>">
                        <?php endforeach; ?>
                    </datalist>
                </div>
                <div class="form-group">
                    <label>Stock Quantity</label>
                    <input type="number" id="newStock" placeholder="e.g. 50" min="0">
                </div>
                <div class="form-group" style="grid-column:1/-1;">
                    <label>Emoji Icon</label>
                    <input type="text" id="newEmoji" placeholder="e.g. ☕ 🥤 🍕 🍔 🍜">
                </div>
            </div>
            <button class="add-product-btn" onclick="addProduct()"><i class="fas fa-plus"></i> Add Product</button>

            <table class="prod-table" id="prodTable">
                <thead><tr><th>Emoji</th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th><th>Del</th></tr></thead>
                <tbody id="prodTableBody">
                <?php foreach($products as $p): ?>
                <tr id="prod-row-<?= $p['id'] ?>">
                    <td style="font-size:20px;"><?= $p['emoji'] ?></td>
                    <td><?= htmlspecialchars($p['name']) ?></td>
                    <td style="color:#8aa0c5;"><?= htmlspecialchars($p['category']) ?></td>
                    <td style="color:#2ecc71;">₱<?= number_format($p['price'],2) ?></td>
                    <td><?= $p['stock'] ?></td>
                    <td><button class="toggle-btn <?= $p['active']?'on':'' ?>" onclick="toggleProduct(<?= $p['id'] ?>,this)"><?= $p['active']?'Active':'Hidden' ?></button></td>
                    <td><button class="del-prod" onclick="deleteProduct(<?= $p['id'] ?>)"><i class="fas fa-trash"></i></button></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Receipt Modal -->
<div class="modal-bg" id="receiptModal">
    <div class="modal-box" style="max-width:380px;">
        <div class="modal-head">
            <h3><i class="fas fa-receipt" style="color:#2ecc71;margin-right:8px;"></i> Receipt</h3>
            <button class="modal-close" onclick="closeReceipt()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body" style="text-align:center;">
            <div class="receipt" id="receiptContent"></div>
            <button class="print-btn" onclick="window.print()"><i class="fas fa-print"></i> Print Receipt</button>
            <button class="print-btn" style="background:#132742;margin-top:8px;" onclick="closeReceipt()">Close</button>
        </div>
    </div>
</div>

<script>
let cart = [];

function addToCart(id, name, price, emoji) {
    const existing = cart.find(i => i.id === id);
    if (existing) existing.qty++;
    else cart.push({id, name, price, emoji, qty: 1});
    renderCart();
}

function renderCart() {
    const el = document.getElementById('cartItems');
    document.getElementById('cartCount').textContent = cart.reduce((s,i)=>s+i.qty,0);
    if (cart.length === 0) {
        el.innerHTML = '<div class="cart-empty"><i class="fas fa-shopping-basket"></i>Cart is empty<br><small style="font-size:12px;">Click a product to add</small></div>';
        document.getElementById('subtotal').textContent = '₱0.00';
        document.getElementById('grandTotal').textContent = '₱0.00';
        document.getElementById('checkoutBtn').disabled = true;
        return;
    }
    let html = '', total = 0;
    cart.forEach((item, idx) => {
        const sub = item.price * item.qty;
        total += sub;
        html += `<div class="cart-item">
            <span style="font-size:20px;">${item.emoji}</span>
            <div class="ci-name">${item.name}<br><small style="color:#8aa0c5;">₱${item.price.toFixed(2)} each</small></div>
            <div class="qty-ctrl">
                <button class="qty-btn" onclick="changeQty(${idx},-1)">−</button>
                <span class="qty-num">${item.qty}</span>
                <button class="qty-btn" onclick="changeQty(${idx},1)">+</button>
            </div>
            <span class="ci-price">₱${sub.toFixed(2)}</span>
            <span class="remove-btn" onclick="removeItem(${idx})"><i class="fas fa-times"></i></span>
        </div>`;
    });
    el.innerHTML = html;
    document.getElementById('subtotal').textContent = '₱' + total.toFixed(2);
    document.getElementById('grandTotal').textContent = '₱' + total.toFixed(2);
    document.getElementById('checkoutBtn').disabled = false;
    calcChange();
}

function changeQty(idx, delta) {
    cart[idx].qty += delta;
    if (cart[idx].qty <= 0) cart.splice(idx, 1);
    renderCart();
}

function removeItem(idx) { cart.splice(idx, 1); renderCart(); }
function clearCart() { cart = []; renderCart(); document.getElementById('cashInput').value=''; document.getElementById('changeDisplay').textContent='₱0.00'; }

function calcChange() {
    const total = cart.reduce((s,i)=>s+(i.price*i.qty),0);
    const cash = parseFloat(document.getElementById('cashInput').value)||0;
    const change = cash - total;
    document.getElementById('changeDisplay').textContent = '₱' + (change >= 0 ? change.toFixed(2) : '0.00');
    document.getElementById('changeDisplay').style.color = change >= 0 ? '#2ecc71' : '#ff4d4d';
}

function checkout() {
    const total = cart.reduce((s,i)=>s+(i.price*i.qty),0);
    const cash = parseFloat(document.getElementById('cashInput').value)||0;
    if (cash < total) { alert('Cash received is less than total!'); return; }

    fetch('pos_checkout.php', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({cart, total, cash, change: cash-total})
    }).then(r=>r.json()).then(d=>{
        if (d.success) {
            showReceipt(d, cash, cash-total);
        } else { alert('Error: ' + d.error); }
    });
}

function showReceipt(d, cash, change) {
    const now = new Date();
    let rows = '';
    cart.forEach(i => {
        rows += `<div class="receipt-row"><span>${i.emoji} ${i.name} x${i.qty}</span><span>₱${(i.price*i.qty).toFixed(2)}</span></div>`;
    });
    document.getElementById('receiptContent').innerHTML = `
        <h2>TheDesktop</h2>
        <p>Management & Analytics Portal</p>
        <hr>
        <div style="font-size:11px;color:#666;margin-bottom:8px;">${now.toLocaleString()}</div>
        <hr>
        ${rows}
        <hr>
        <div class="receipt-row receipt-total"><span>TOTAL</span><span>₱${cart.reduce((s,i)=>s+(i.price*i.qty),0).toFixed(2)}</span></div>
        <div class="receipt-row"><span>Cash</span><span>₱${cash.toFixed(2)}</span></div>
        <div class="receipt-row"><span>Change</span><span>₱${change.toFixed(2)}</span></div>
        <hr>
        <div class="receipt-footer">Thank you for your purchase!<br>Come back soon 😊</div>
    `;
    document.getElementById('receiptModal').classList.add('show');
    clearCart();
    location.reload();
}

function closeReceipt() {
    document.getElementById('receiptModal').classList.remove('show');
}

function filterCat(cat, el) {
    document.querySelectorAll('.cat-tab').forEach(t=>t.classList.remove('active'));
    el.classList.add('active');
    document.querySelectorAll('.product-card').forEach(c=>{
        c.style.display = (cat==='all'||c.dataset.cat===cat)?'block':'none';
    });
}

function filterProducts() {
    const q = document.getElementById('searchInput').value.toLowerCase();
    document.querySelectorAll('.product-card').forEach(c=>{
        c.style.display = c.dataset.name.toLowerCase().includes(q)?'block':'none';
    });
}

function addProduct() {
    const name = document.getElementById('newName').value.trim();
    const price = document.getElementById('newPrice').value;
    const category = document.getElementById('newCategory').value.trim();
    const stock = document.getElementById('newStock').value;
    const emoji = document.getElementById('newEmoji').value.trim() || '🛍️';
    if (!name || !price || !category) { alert('Please fill in Name, Price, and Category.'); return; }
    fetch('pos_product.php', {
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify({action:'add', name, price, category, stock, emoji})
    }).then(r=>r.json()).then(d=>{
        if(d.success) location.reload();
        else alert('Error: '+d.error);
    });
}

function deleteProduct(id) {
    if(!confirm('Delete this product?')) return;
    fetch('pos_product.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'delete',id})})
        .then(r=>r.json()).then(d=>{ if(d.success) { document.getElementById('prod-row-'+id).remove(); } });
}

function toggleProduct(id, btn) {
    fetch('pos_product.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'toggle',id})})
        .then(r=>r.json()).then(d=>{
            if(d.success){ btn.classList.toggle('on'); btn.textContent=btn.classList.contains('on')?'Active':'Hidden'; }
        });
}
</script>
</body>
</html>

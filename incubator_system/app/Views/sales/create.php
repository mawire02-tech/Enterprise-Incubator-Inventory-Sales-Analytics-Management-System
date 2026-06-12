<?php /* sales/create.php */ ?>

<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <h1 class="page-title">New Sale <span class="badge text-bg-primary ms-2 fs-6">POS</span></h1>
        <p class="page-subtitle mb-0">Build the cart then process payment</p>
    </div>
    <a href="<?= APP_URL ?>/sales" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back to Sales
    </a>
</div>

<form id="posForm">
    <?= \App\Helpers\CSRF::field() ?>

<div class="row g-3">

    <!-- ── LEFT: Product selection ─────────────────────────── -->
    <div class="col-12 col-lg-7">

        <!-- Product search/filter -->
        <div class="card mb-3">
            <div class="card-body py-2">
                <div class="d-flex gap-2">
                    <input type="text" id="productSearch" class="form-control form-control-sm"
                           placeholder="Search product by name or SKU…" autocomplete="off">
                    <select id="categoryFilter" class="form-select form-select-sm" style="max-width:160px">
                        <option value="">All Categories</option>
                        <?php
                        $cats = array_unique(array_column($products, 'category'));
                        foreach ($cats as $cat):
                            if ($cat):
                        ?>
                        <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
                        <?php endif; endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- Product grid -->
        <div class="row g-2" id="productGrid">
        <?php foreach ($products as $p): ?>
            <?php $inStock = (int)$p['stock_qty'] > 0; ?>
            <div class="col-6 col-sm-4 col-md-3 product-tile"
                 data-name="<?= strtolower(htmlspecialchars($p['name'])) ?>"
                 data-category="<?= htmlspecialchars($p['category']) ?>">
                <div class="pos-product-card <?= !$inStock ? 'opacity-50' : '' ?>"
                     onclick="<?= $inStock ? "addToCart({$p['id']}, ".htmlspecialchars(json_encode($p)).")" : "App.toast('Out of stock','warning')" ?>">
                    <div class="product-name mb-1"><?= htmlspecialchars($p['name']) ?></div>
                    <div class="product-price mb-1"><?= $currency . number_format((float)$p['min_price'], 2) ?></div>
                    <div class="stock-qty <?= !$inStock ? 'text-danger' : 'text-muted' ?>">
                        <?= $inStock ? 'Stock: '.(int)$p['stock_qty'] : 'Out of stock' ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    </div>

    <!-- ── RIGHT: Cart ─────────────────────────────────────── -->
    <div class="col-12 col-lg-5">
        <div class="card sticky-top" style="top:70px">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span><i class="bi bi-cart3 me-2 text-primary"></i>Cart</span>
                <button type="button" class="btn btn-xs btn-outline-danger" onclick="clearCart()">
                    <i class="bi bi-trash me-1"></i>Clear
                </button>
            </div>

            <!-- Cart items -->
            <div id="cartItems" style="max-height:300px;overflow-y:auto">
                <div id="cartEmpty" class="text-center text-muted py-5 small">
                    <i class="bi bi-cart d-block fs-2 mb-2 opacity-25"></i>Cart is empty — click a product to add
                </div>
                <table class="table table-sm mb-0 small d-none" id="cartTable">
                    <thead><tr>
                        <th>Product</th>
                        <th class="text-center" style="width:90px">Qty</th>
                        <th class="text-end">Price</th>
                        <th class="text-end">Total</th>
                        <th></th>
                    </tr></thead>
                    <tbody id="cartBody"></tbody>
                </table>
            </div>

            <div class="card-footer">
                <!-- Customer -->
                <div class="mb-3">
                    <label class="form-label mb-1">Customer</label>
                    <div class="input-group input-group-sm">
                        <input type="text" id="customerSearch" class="form-control" placeholder="Search customer…" autocomplete="off">
                        <button class="btn btn-outline-secondary" type="button" onclick="clearCustomer()"><i class="bi bi-x"></i></button>
                    </div>
                    <div id="customerDropdown" class="dropdown-menu w-100" style="max-height:150px;overflow-y:auto"></div>
                    <input type="hidden" id="customerId" name="customer_id">
                    <div id="customerSelected" class="alert alert-success py-1 px-2 small mt-1 d-none"></div>
                    <input type="text" name="customer_name" id="customerName" class="form-control form-control-sm mt-1 d-none"
                           placeholder="Walk-in customer name (optional)">
                </div>

                <!-- Totals -->
                <table class="table table-sm small mb-2">
                    <tr><td class="text-muted">Subtotal</td><td class="text-end fw-semibold" id="tSubtotal"><?= $currency ?>0.00</td></tr>
                    <tr>
                        <td class="text-muted">
                            Discount
                            <select name="discount_type" id="discountType" class="form-select form-select-sm d-inline-block ms-1" style="width:auto">
                                <option value="none">None</option>
                                <option value="percent">%</option>
                                <option value="fixed">$</option>
                            </select>
                        </td>
                        <td class="text-end">
                            <input type="number" name="discount_value" id="discountValue" class="form-control form-control-sm text-end"
                                   style="width:80px;display:inline-block" min="0" step="0.01" value="0">
                        </td>
                    </tr>
                    <?php if ($tax_rate > 0): ?>
                    <tr><td class="text-muted">Tax (<?= $tax_rate ?>%)</td><td class="text-end" id="tTax"><?= $currency ?>0.00</td></tr>
                    <?php endif; ?>
                    <tr class="table-active"><td class="fw-bold">TOTAL</td><td class="text-end fw-bold fs-5 text-primary" id="tTotal"><?= $currency ?>0.00</td></tr>
                </table>

                <!-- Payment -->
                <div class="row g-2 mb-3">
                    <div class="col-7">
                        <label class="form-label mb-1" style="font-size:.75rem">Payment Method</label>
                        <select name="payment_method" class="form-select form-select-sm" id="paymentMethod">
                            <option value="cash">Cash</option>
                            <option value="ecocash">EcoCash</option>
                            <option value="onemoney">OneMoney</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="card">Card</option>
                            <option value="credit">Credit</option>
                        </select>
                    </div>
                    <div class="col-5">
                        <label class="form-label mb-1" style="font-size:.75rem">Amount Paid</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><?= $currency ?></span>
                            <input type="number" name="amount_paid" id="amountPaid" class="form-control text-end"
                                   min="0" step="0.01" value="0">
                        </div>
                    </div>
                </div>

                <!-- Change due -->
                <div id="changeRow" class="alert alert-success py-1 px-2 small mb-3 d-none">
                    Change due: <strong id="changeDue"></strong>
                </div>

                <div class="mb-2">
                    <input type="text" name="notes" class="form-control form-control-sm" placeholder="Sale notes (optional)">
                </div>

                <button type="button" class="btn btn-primary w-100" id="processBtn" onclick="processSale()" disabled>
                    <i class="bi bi-check-circle me-2"></i>Process Sale
                </button>

                <input type="hidden" name="tax_rate" value="<?= $tax_rate ?>">
                <input type="hidden" id="cartData" name="items" value="[]">
                <input type="hidden" name="sale_date" value="<?= date('Y-m-d') ?>">
            </div>
        </div>
    </div>
</div>
</form>

<!-- Receipt Modal -->
<div class="modal fade" id="receiptModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header border-0 bg-success text-white">
                <h6 class="modal-title"><i class="bi bi-check-circle me-2"></i>Sale Completed!</h6>
            </div>
            <div class="modal-body text-center">
                <div class="fs-4 fw-bold text-success mb-1" id="receiptTotal"></div>
                <div class="text-muted small mb-3" id="receiptInvoice"></div>
                <div class="d-grid gap-2">
                    <a href="#" class="btn btn-sm btn-outline-primary" id="printReceiptBtn" target="_blank">
                        <i class="bi bi-printer me-1"></i>Print Invoice
                    </a>
                    <button class="btn btn-sm btn-success" onclick="newSale()">
                        <i class="bi bi-cart-plus me-1"></i>New Sale
                    </button>
                    <a href="<?= APP_URL ?>/sales" class="btn btn-sm btn-outline-secondary">View All Sales</a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>.btn-xs{padding:.2rem .45rem;font-size:.75rem;}</style>

<script>
const TAX_RATE   = <?= (float)$tax_rate ?>;
const CURRENCY   = '<?= $currency ?>';
const APP_URL    = '<?= APP_URL ?>';
let cart = [];
let productBatchCache = {};

// ── Add to cart ─────────────────────────────────────────────
async function addToCart(productId, product) {
    // Check if already in cart
    const existing = cart.find(i => i.product_id === productId);
    if (existing) {
        existing.quantity++;
        renderCart();
        return;
    }

    // Fetch batch info
    let batches;
    try {
        const res = await App.fetch(`${APP_URL}/inventory/products/${productId}/batches`);
        batches = res.data;
        productBatchCache[productId] = batches;
    } catch(e) { App.toast('Failed to load batch info.','danger'); return; }

    if (!batches.length) { App.toast('No stock available.','warning'); return; }
    const batch = batches[0]; // FIFO: first batch

    cart.push({
        product_id:   productId,
        batch_id:     batch.id,
        product_name: product.name,
        quantity:     1,
        unit_price:   parseFloat(batch.selling_price),
        max_qty:      batch.quantity_current,
    });

    renderCart();
    calcTotals();
}

function removeFromCart(idx) {
    cart.splice(idx, 1);
    renderCart();
    calcTotals();
}

function updateQty(idx, delta) {
    const item = cart[idx];
    if (!item) return;
    item.quantity = Math.max(1, Math.min(item.max_qty, item.quantity + delta));
    renderCart();
    calcTotals();
}

function setPrice(idx, val) {
    cart[idx].unit_price = parseFloat(val) || 0;
    calcTotals();
}

// ── Render cart table ────────────────────────────────────────
function renderCart() {
    const empty  = document.getElementById('cartEmpty');
    const table  = document.getElementById('cartTable');
    const tbody  = document.getElementById('cartBody');
    const btn    = document.getElementById('processBtn');

    if (!cart.length) {
        empty.classList.remove('d-none');
        table.classList.add('d-none');
        btn.disabled = true;
        return;
    }

    empty.classList.add('d-none');
    table.classList.remove('d-none');
    btn.disabled = false;

    tbody.innerHTML = cart.map((item, idx) => `
        <tr class="cart-item-row">
            <td class="text-truncate" style="max-width:100px">${item.product_name}</td>
            <td class="text-center">
                <div class="input-group input-group-sm" style="width:85px">
                    <button class="btn btn-outline-secondary btn-sm px-1" type="button" onclick="updateQty(${idx},-1)">−</button>
                    <input type="number" class="form-control text-center px-1" value="${item.quantity}" min="1" max="${item.max_qty}"
                           onchange="cart[${idx}].quantity=Math.max(1,Math.min(${item.max_qty},parseInt(this.value)||1));calcTotals()">
                    <button class="btn btn-outline-secondary btn-sm px-1" type="button" onclick="updateQty(${idx},1)">+</button>
                </div>
            </td>
            <td class="text-end" style="width:80px">
                <input type="number" class="form-control form-control-sm text-end" value="${item.unit_price.toFixed(2)}"
                       style="width:70px" min="0" step="0.01" onchange="setPrice(${idx},this.value);calcTotals()">
            </td>
            <td class="text-end fw-semibold">${CURRENCY}${(item.quantity * item.unit_price).toFixed(2)}</td>
            <td><button type="button" class="btn btn-xs btn-outline-danger" onclick="removeFromCart(${idx})">
                <i class="bi bi-x"></i></button></td>
        </tr>`).join('');
}

// ── Totals ───────────────────────────────────────────────────
function calcTotals() {
    const subtotal = cart.reduce((s, i) => s + i.quantity * i.unit_price, 0);
    const discType = document.getElementById('discountType').value;
    const discVal  = parseFloat(document.getElementById('discountValue').value) || 0;

    let discAmt = 0;
    if (discType === 'percent') discAmt = subtotal * (discVal / 100);
    else if (discType === 'fixed') discAmt = Math.min(discVal, subtotal);

    const tax   = (subtotal - discAmt) * (TAX_RATE / 100);
    const total = subtotal - discAmt + tax;

    document.getElementById('tSubtotal').textContent = CURRENCY + subtotal.toFixed(2);
    const taxEl = document.getElementById('tTax');
    if (taxEl) taxEl.textContent = CURRENCY + tax.toFixed(2);
    document.getElementById('tTotal').textContent = CURRENCY + total.toFixed(2);

    // Auto-fill amount paid
    const paidEl = document.getElementById('amountPaid');
    if (!parseFloat(paidEl.value)) paidEl.value = total.toFixed(2);

    calcChange(total);
    document.getElementById('cartData').value = JSON.stringify(cart.map(i => ({
        product_id: i.product_id, batch_id: i.batch_id,
        quantity: i.quantity, unit_price: i.unit_price,
    })));
}

function calcChange(total) {
    const paid   = parseFloat(document.getElementById('amountPaid').value) || 0;
    const change = paid - (total || 0);
    const row    = document.getElementById('changeRow');
    if (change > 0) {
        row.classList.remove('d-none');
        document.getElementById('changeDue').textContent = CURRENCY + change.toFixed(2);
    } else {
        row.classList.add('d-none');
    }
}

function clearCart() {
    cart = [];
    renderCart();
    calcTotals();
}

// ── Process sale ─────────────────────────────────────────────
async function processSale() {
    if (!cart.length) { App.toast('Cart is empty.','warning'); return; }

    const form    = document.getElementById('posForm');
    const btn     = document.getElementById('processBtn');
    const origTxt = btn.innerHTML;
    btn.disabled  = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Processing…';

    const fd = new FormData(form);
    const body = {
        items:           JSON.parse(fd.get('items') || '[]'),
        customer_id:     fd.get('customer_id') || null,
        customer_name:   fd.get('customer_name') || null,
        payment_method:  fd.get('payment_method'),
        discount_type:   fd.get('discount_type'),
        discount_value:  fd.get('discount_value'),
        tax_rate:        fd.get('tax_rate'),
        amount_paid:     fd.get('amount_paid'),
        notes:           fd.get('notes'),
        sale_date:       fd.get('sale_date'),
        _csrf_token:     fd.get('_csrf_token'),
    };

    try {
        const res = await App.fetch(`${APP_URL}/sales/create`, {
            method: 'POST', body: body
        });

        // Show receipt
        document.getElementById('receiptTotal').textContent = CURRENCY + parseFloat(res.data.total).toFixed(2);
        document.getElementById('receiptInvoice').textContent = 'Invoice: ' + res.data.invoice_number;
        document.getElementById('printReceiptBtn').href = `${APP_URL}/sales/${res.data.sale_id}/print`;
        new bootstrap.Modal(document.getElementById('receiptModal')).show();
        clearCart();
    } catch(e) {
        App.toast(e.message, 'danger');
        btn.disabled  = false;
        btn.innerHTML = origTxt;
    }
}

function newSale() {
    bootstrap.Modal.getInstance(document.getElementById('receiptModal'))?.hide();
    clearCart();
    document.getElementById('customerSearch').value = '';
    clearCustomer();
}

// ── Customer search ──────────────────────────────────────────
let custTimer = null;
document.getElementById('customerSearch').addEventListener('input', function() {
    clearTimeout(custTimer);
    const q = this.value.trim();
    if (q.length < 2) { document.getElementById('customerDropdown').style.display='none'; return; }
    custTimer = setTimeout(async () => {
        try {
            const res = await App.fetch(`${APP_URL}/customers/search?q=${encodeURIComponent(q)}`);
            const dd  = document.getElementById('customerDropdown');
            if (!res.data.length) { dd.style.display='none'; return; }
            dd.innerHTML = res.data.map(c =>
                `<a class="dropdown-item small" href="#" onclick="selectCustomer(${c.id},'${c.full_name}','${c.phone}');return false">
                    <strong>${c.full_name}</strong> <span class="text-muted">${c.phone}</span>
                </a>`).join('');
            dd.style.display = 'block';
        } catch(e) {}
    }, 300);
});

function selectCustomer(id, name, phone) {
    document.getElementById('customerId').value  = id;
    document.getElementById('customerSearch').value = name;
    document.getElementById('customerDropdown').style.display = 'none';
    document.getElementById('customerSelected').textContent = `✓ ${name} — ${phone}`;
    document.getElementById('customerSelected').classList.remove('d-none');
    document.getElementById('customerName').classList.add('d-none');
}

function clearCustomer() {
    document.getElementById('customerId').value = '';
    document.getElementById('customerSearch').value = '';
    document.getElementById('customerSelected').classList.add('d-none');
    document.getElementById('customerName').classList.remove('d-none');
}

// ── Product search/filter ────────────────────────────────────
document.getElementById('productSearch').addEventListener('input', filterProducts);
document.getElementById('categoryFilter').addEventListener('change', filterProducts);

function filterProducts() {
    const q   = document.getElementById('productSearch').value.toLowerCase();
    const cat = document.getElementById('categoryFilter').value.toLowerCase();
    document.querySelectorAll('.product-tile').forEach(el => {
        const name   = el.dataset.name || '';
        const eCat   = (el.dataset.category || '').toLowerCase();
        const matchQ = !q   || name.includes(q);
        const matchC = !cat || eCat === cat;
        el.style.display = matchQ && matchC ? '' : 'none';
    });
}

// ── Event listeners ──────────────────────────────────────────
document.getElementById('discountType').addEventListener('change', calcTotals);
document.getElementById('discountValue').addEventListener('input', calcTotals);
document.getElementById('amountPaid').addEventListener('input', () => {
    const subtotal = cart.reduce((s,i) => s+i.quantity*i.unit_price,0);
    const total    = subtotal * (1 + TAX_RATE/100);
    calcChange(total);
});
</script>

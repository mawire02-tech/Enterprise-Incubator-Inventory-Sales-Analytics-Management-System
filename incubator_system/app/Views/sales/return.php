<?php /* sales/return.php */
function fmt($v){return '$'.number_format((float)$v,2);}
?>
<div class="d-flex align-items-center mb-4">
    <a href="<?= APP_URL ?>/sales/<?= $sale['id'] ?>" class="btn btn-sm btn-outline-secondary me-3"><i class="bi bi-arrow-left"></i></a>
    <div>
        <h1 class="page-title">Process Return</h1>
        <p class="page-subtitle mb-0">Invoice <?= htmlspecialchars($sale['invoice_number']) ?></p>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-8">
        <div class="card">
            <div class="card-header"><i class="bi bi-arrow-return-left me-2 text-warning"></i>Return Details</div>
            <div class="card-body" id="returnForm">

                <div class="mb-3">
                    <label class="form-label">Reason for Return <span class="text-danger">*</span></label>
                    <textarea id="returnReason" class="form-control" rows="2" required placeholder="Describe why items are being returned…"></textarea>
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="restockCheck" checked>
                        <label class="form-check-label small" for="restockCheck">
                            Return items to inventory (restock)
                        </label>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Select Items to Return</label>
                    <div class="table-responsive">
                        <table class="table table-sm small mb-0">
                            <thead><tr>
                                <th><input type="checkbox" id="selectAll" onclick="toggleAll(this)"></th>
                                <th>Product</th>
                                <th class="text-center">Sold Qty</th>
                                <th class="text-center">Return Qty</th>
                                <th class="text-end">Unit Price</th>
                                <th class="text-end">Return Amount</th>
                            </tr></thead>
                            <tbody>
                            <?php foreach ($sale['items'] as $item): ?>
                            <tr>
                                <td><input type="checkbox" class="item-check" data-id="<?= $item['id'] ?>"
                                           data-price="<?= $item['unit_price'] ?>"
                                           data-max="<?= $item['quantity'] ?>" onchange="updateTotal()"></td>
                                <td><?= htmlspecialchars($item['product_name']) ?></td>
                                <td class="text-center"><?= $item['quantity'] ?></td>
                                <td class="text-center">
                                    <input type="number" class="form-control form-control-sm text-center return-qty"
                                           data-id="<?= $item['id'] ?>" data-price="<?= $item['unit_price'] ?>"
                                           min="1" max="<?= $item['quantity'] ?>" value="<?= $item['quantity'] ?>"
                                           style="width:70px" onchange="updateTotal()" disabled>
                                </td>
                                <td class="text-end"><?= fmt($item['unit_price']) ?></td>
                                <td class="text-end fw-semibold return-line" data-id="<?= $item['id'] ?>">—</td>
                            </tr>
                            <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr class="table-warning">
                                    <td colspan="5" class="text-end fw-bold">Total Return Amount:</td>
                                    <td class="text-end fw-bold" id="totalReturn">$0.00</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <button class="btn btn-warning" onclick="submitReturn()">
                    <i class="bi bi-arrow-return-left me-1"></i>Process Return
                </button>
                <a href="<?= APP_URL ?>/sales/<?= $sale['id'] ?>" class="btn btn-outline-secondary ms-2">Cancel</a>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="card">
            <div class="card-header"><i class="bi bi-receipt me-2 text-primary"></i>Original Sale</div>
            <div class="card-body small">
                <table class="table table-sm mb-0">
                    <tr><td class="text-muted">Invoice</td><td><code><?= htmlspecialchars($sale['invoice_number']) ?></code></td></tr>
                    <tr><td class="text-muted">Date</td><td><?= date('d/m/Y',strtotime($sale['sale_date'])) ?></td></tr>
                    <tr><td class="text-muted">Customer</td><td><?= htmlspecialchars($sale['customer_full_name']??$sale['customer_name']??'Walk-in') ?></td></tr>
                    <tr><td class="text-muted">Total</td><td class="fw-bold"><?= fmt($sale['total_amount']) ?></td></tr>
                    <tr><td class="text-muted">Payment</td><td><?= ucfirst($sale['payment_method']) ?></td></tr>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function toggleAll(cb) {
    document.querySelectorAll('.item-check').forEach(el => {
        el.checked = cb.checked;
        const qtyEl = document.querySelector(`.return-qty[data-id="${el.dataset.id}"]`);
        if (qtyEl) qtyEl.disabled = !cb.checked;
    });
    updateTotal();
}

document.querySelectorAll('.item-check').forEach(cb => {
    cb.addEventListener('change', () => {
        const qtyEl = document.querySelector(`.return-qty[data-id="${cb.dataset.id}"]`);
        if (qtyEl) qtyEl.disabled = !cb.checked;
        updateTotal();
    });
});

function updateTotal() {
    let total = 0;
    document.querySelectorAll('.item-check:checked').forEach(cb => {
        const qty     = parseInt(document.querySelector(`.return-qty[data-id="${cb.dataset.id}"]`).value)||0;
        const price   = parseFloat(cb.dataset.price)||0;
        const lineAmt = qty * price;
        document.querySelector(`.return-line[data-id="${cb.dataset.id}"]`).textContent = '$'+lineAmt.toFixed(2);
        total += lineAmt;
    });
    document.querySelectorAll('.item-check:not(:checked)').forEach(cb => {
        document.querySelector(`.return-line[data-id="${cb.dataset.id}"]`).textContent = '—';
    });
    document.getElementById('totalReturn').textContent = '$'+total.toFixed(2);
}

async function submitReturn() {
    const reason  = document.getElementById('returnReason').value.trim();
    const restock = document.getElementById('restockCheck').checked;

    if (!reason) { App.toast('Please provide a return reason.','warning'); return; }

    const items = [];
    document.querySelectorAll('.item-check:checked').forEach(cb => {
        const qty = parseInt(document.querySelector(`.return-qty[data-id="${cb.dataset.id}"]`).value)||0;
        if (qty > 0) items.push({ sale_item_id: parseInt(cb.dataset.id), quantity: qty });
    });

    if (!items.length) { App.toast('Select at least one item to return.','warning'); return; }

    const ok = await App.confirm(`Process return of ${items.length} item(s)?`, 'Confirm Return');
    if (!ok) return;

    try {
        const res = await App.fetch(`${APP_URL}/sales/<?= $sale['id'] ?>/return`, {
            method: 'POST',
            body: { reason, restock: restock?1:0, items, _csrf_token: CSRF_TOKEN }
        });
        App.toast('Return processed: ' + res.data.return_number,'success');
        setTimeout(() => window.location = `${APP_URL}/sales/<?= $sale['id'] ?>`, 1000);
    } catch(e) { App.toast(e.message,'danger'); }
}
</script>

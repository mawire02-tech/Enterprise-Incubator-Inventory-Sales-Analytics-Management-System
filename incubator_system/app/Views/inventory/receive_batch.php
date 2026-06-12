<!-- ═══ RECEIVE BATCH ═══════════════════════════════════════════ -->
<?php if (!isset($batch)): // receive_batch.php ?>

<div class="d-flex align-items-center mb-4">
    <a href="<?= APP_URL ?>/inventory/batches" class="btn btn-sm btn-outline-secondary me-3"><i class="bi bi-arrow-left"></i></a>
    <div>
        <h1 class="page-title">Receive Stock</h1>
        <p class="page-subtitle mb-0">Record a new incoming inventory batch</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-8">
        <div class="card">
            <div class="card-header">Batch Details</div>
            <div class="card-body">
                <form method="POST" action="<?= APP_URL ?>/inventory/batches/receive" id="receiveBatchForm">
                    <?= \App\Helpers\CSRF::field() ?>

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Product <span class="text-danger">*</span></label>
                            <select name="product_id" class="form-select" required id="productSelect">
                                <option value="">— Select Product —</option>
                                <?php foreach ($products as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?> (<?= $p['sku'] ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-sm-4">
                            <label class="form-label">Quantity <span class="text-danger">*</span></label>
                            <input type="number" name="quantity" class="form-control" min="1" required
                                   placeholder="e.g. 20" id="qtyInput">
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label">Cost Price (per unit) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" name="cost_price" class="form-control" min="0.01" step="0.01"
                                       required placeholder="0.00" id="costInput">
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label">Selling Price (per unit) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" name="selling_price" class="form-control" min="0.01" step="0.01"
                                       required placeholder="0.00" id="sellInput">
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label">Acquisition Date <span class="text-danger">*</span></label>
                            <input type="date" name="acquisition_date" class="form-control"
                                   value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Expiry / Warranty Date</label>
                            <input type="date" name="expiry_date" class="form-control">
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label">Supplier Name</label>
                            <input type="text" name="supplier_name" class="form-control" placeholder="e.g. FarmTech Suppliers">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Supplier Reference / Invoice #</label>
                            <input type="text" name="supplier_ref" class="form-control" placeholder="Supplier invoice number">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Any additional notes about this batch…"></textarea>
                        </div>
                    </div>

                    <!-- Live batch summary -->
                    <div class="alert alert-info mt-3 d-none" id="batchSummary">
                        <div class="fw-semibold small mb-1"><i class="bi bi-info-circle me-1"></i>Batch Summary Preview</div>
                        <div class="row g-2 small">
                            <div class="col-4">Total Cost: <strong id="previewCost">$0.00</strong></div>
                            <div class="col-4">Retail Value: <strong id="previewRetail">$0.00</strong></div>
                            <div class="col-4">Potential Profit: <strong id="previewProfit" class="text-success">$0.00</strong></div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-box-arrow-in-down me-1"></i>Receive Batch
                        </button>
                        <a href="<?= APP_URL ?>/inventory/batches" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="card border-success-subtle">
            <div class="card-header text-success"><i class="bi bi-info-circle me-2"></i>Important Notes</div>
            <div class="card-body small text-muted">
                <ul class="ps-3 mb-0">
                    <li class="mb-2">Each batch has its own cost price and selling price for accurate profit tracking.</li>
                    <li class="mb-2">Stock is deducted using <strong>FIFO</strong> (oldest batches first).</li>
                    <li class="mb-2">When a batch reaches zero, the system auto-calculates P&L and can archive it.</li>
                    <li>The batch code will be auto-generated.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
function calcSummary() {
    const qty  = parseFloat(document.getElementById('qtyInput').value) || 0;
    const cost = parseFloat(document.getElementById('costInput').value) || 0;
    const sell = parseFloat(document.getElementById('sellInput').value) || 0;

    const summary = document.getElementById('batchSummary');
    if (qty > 0 && (cost > 0 || sell > 0)) {
        summary.classList.remove('d-none');
        document.getElementById('previewCost').textContent   = '$' + (qty * cost).toFixed(2);
        document.getElementById('previewRetail').textContent = '$' + (qty * sell).toFixed(2);
        const profit = (sell - cost) * qty;
        const el = document.getElementById('previewProfit');
        el.textContent = '$' + profit.toFixed(2);
        el.className = profit >= 0 ? 'text-success fw-bold' : 'text-danger fw-bold';
    } else {
        summary.classList.add('d-none');
    }
}
['qtyInput','costInput','sellInput'].forEach(id => {
    document.getElementById(id)?.addEventListener('input', calcSummary);
});
</script>

<?php else: // batch_detail.php — when $batch is set ?>

<!-- Included from batch_detail.php directly -->
<?php endif; ?>

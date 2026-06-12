<?php function fmt($v){return '$'.number_format((float)$v,2);} ?>

<div class="d-flex align-items-center mb-4">
    <a href="<?= APP_URL ?>/inventory/batches/<?= $batch['id'] ?>" class="btn btn-sm btn-outline-secondary me-3"><i class="bi bi-arrow-left"></i></a>
    <div>
        <h1 class="page-title">Adjust Stock</h1>
        <p class="page-subtitle mb-0">Batch <code><?= htmlspecialchars($batch['batch_code']) ?></code> — <?= htmlspecialchars($batch['product_name']) ?></p>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-7">
        <div class="card">
            <div class="card-header">Adjustment Details</div>
            <div class="card-body">

                <div class="alert alert-info d-flex gap-3 mb-4">
                    <i class="bi bi-boxes fs-4"></i>
                    <div>
                        <div class="fw-semibold">Current Stock: <?= number_format($batch['quantity_current']) ?> units</div>
                        <div class="small text-muted">Cost: <?= fmt($batch['cost_price']) ?> &nbsp;/&nbsp; Sell: <?= fmt($batch['selling_price']) ?></div>
                    </div>
                </div>

                <form method="POST" action="<?= APP_URL ?>/inventory/batches/<?= $batch['id'] ?>/adjust">
                    <?= \App\Helpers\CSRF::field() ?>

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Adjustment Type <span class="text-danger">*</span></label>
                            <div class="row g-2" id="typeGroup">
                                <?php
                                $types = [
                                    ['add',        'Add Stock',    'bi-plus-circle', 'success', 'Add additional units (found stock, new delivery, etc.)'],
                                    ['remove',      'Remove Stock', 'bi-dash-circle', 'warning', 'Remove units for any non-sale reason'],
                                    ['damage',      'Mark Damaged', 'bi-exclamation-octagon', 'danger', 'Units that are broken/unusable — recorded as a loss'],
                                    ['correction',  'Correction',   'bi-pencil-square','info',  'Set stock to a specific count (physical count correction)'],
                                ];
                                foreach ($types as [$val, $label, $icon, $col, $desc]):
                                ?>
                                <div class="col-6 col-sm-3">
                                    <input type="radio" class="btn-check" name="adjustment_type" id="type_<?= $val ?>" value="<?= $val ?>" required>
                                    <label class="btn btn-outline-<?= $col ?> w-100 d-flex flex-column align-items-center py-3" for="type_<?= $val ?>">
                                        <i class="bi <?= $icon ?> fs-4 mb-1"></i>
                                        <span class="small fw-semibold"><?= $label ?></span>
                                    </label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="form-text mt-2" id="typeHelp">Select an adjustment type above.</div>
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label">Quantity <span class="text-danger">*</span></label>
                            <input type="number" name="quantity" class="form-control" min="1" required
                                   placeholder="Number of units" id="adjQty">
                        </div>

                        <div class="col-12" id="newQtyPreview" style="display:none">
                            <div class="alert alert-secondary py-2 small">
                                New stock count will be: <strong id="previewNewQty">—</strong>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Reason <span class="text-danger">*</span></label>
                            <input type="text" name="reason" class="form-control" required minlength="5"
                                   placeholder="Briefly explain why this adjustment is needed">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Additional details (optional)"></textarea>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-warning" onclick="return confirm('Confirm this stock adjustment?')">
                            <i class="bi bi-check-lg me-1"></i>Apply Adjustment
                        </button>
                        <a href="<?= APP_URL ?>/inventory/batches/<?= $batch['id'] ?>" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-5">
        <div class="card border-warning-subtle">
            <div class="card-header text-warning"><i class="bi bi-exclamation-triangle me-2"></i>Adjustment Rules</div>
            <div class="card-body small text-muted">
                <?php foreach ($types as [$val, $label, $icon, $col, $desc]): ?>
                <div class="d-flex gap-2 mb-3">
                    <i class="bi <?= $icon ?> text-<?= $col ?> mt-1 flex-shrink-0"></i>
                    <div><strong><?= $label ?>:</strong> <?= $desc ?></div>
                </div>
                <?php endforeach; ?>
                <hr>
                <p class="mb-0">All adjustments are <strong>logged in the Audit Trail</strong> with the reason provided.</p>
            </div>
        </div>
    </div>
</div>

<script>
const currentQty = <?= (int)$batch['quantity_current'] ?>;
const typeHelp = {
    add:        'Enter how many units to <strong class="text-success">add</strong> to current stock.',
    remove:     'Enter how many units to <strong class="text-warning">remove</strong> from current stock.',
    damage:     'Enter how many units are <strong class="text-danger">damaged/unusable</strong>. These will be logged as losses.',
    correction: 'Enter the <strong class="text-info">new total stock count</strong> after physical count.',
};

document.querySelectorAll('input[name="adjustment_type"]').forEach(r => {
    r.addEventListener('change', updatePreview);
});
document.getElementById('adjQty').addEventListener('input', updatePreview);

function updatePreview() {
    const type = document.querySelector('input[name="adjustment_type"]:checked')?.value;
    const qty  = parseInt(document.getElementById('adjQty').value) || 0;
    const helpEl = document.getElementById('typeHelp');
    const previewEl = document.getElementById('newQtyPreview');
    const previewQtyEl = document.getElementById('previewNewQty');

    if (type) helpEl.innerHTML = typeHelp[type] || '';

    if (type && qty > 0) {
        previewEl.style.display = '';
        let newQty;
        switch (type) {
            case 'add':        newQty = currentQty + qty; break;
            case 'remove':
            case 'damage':     newQty = Math.max(0, currentQty - qty); break;
            case 'correction': newQty = qty; break;
        }
        previewQtyEl.textContent = newQty.toLocaleString() + ' units';
        previewQtyEl.className   = newQty < currentQty ? 'text-danger fw-bold' : 'text-success fw-bold';
    } else {
        previewEl.style.display = 'none';
    }
}
</script>

<?php
function fmt($v) { return '$' . number_format((float)$v, 2); }
function fmtN($v) { return number_format((int)$v); }
$pct = $batch['quantity_received'] > 0 ? round(($batch['quantity_current'] / $batch['quantity_received']) * 100) : 0;
$mvtIcons = [
    'receive'      => ['bi-box-arrow-in-down','text-success'],
    'sale'         => ['bi-cart-dash','text-primary'],
    'return'       => ['bi-arrow-return-left','text-warning'],
    'adjustment'   => ['bi-sliders','text-info'],
    'damage'       => ['bi-exclamation-octagon','text-danger'],
    'transfer_in'  => ['bi-arrow-left-circle','text-success'],
    'transfer_out' => ['bi-arrow-right-circle','text-warning'],
];
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center">
        <a href="<?= APP_URL ?>/inventory/batches" class="btn btn-sm btn-outline-secondary me-3"><i class="bi bi-arrow-left"></i></a>
        <div>
            <h1 class="page-title">Batch: <code><?= htmlspecialchars($batch['batch_code']) ?></code></h1>
            <p class="page-subtitle mb-0"><?= htmlspecialchars($batch['product_name']) ?> &nbsp;•&nbsp; <?= htmlspecialchars($batch['sku']) ?></p>
        </div>
    </div>
    <div class="d-flex gap-2">
        <?php if ($batch['status'] === 'active' && \App\Helpers\Auth::can('inventory.adjust')): ?>
        <a href="<?= APP_URL ?>/inventory/batches/<?= $batch['id'] ?>/adjust" class="btn btn-sm btn-outline-warning">
            <i class="bi bi-sliders me-1"></i>Adjust Stock
        </a>
        <?php endif; ?>
        <?php if ($batch['status'] === 'depleted' && \App\Helpers\Auth::can('inventory.edit')): ?>
        <form method="POST" action="<?= APP_URL ?>/inventory/batches/<?= $batch['id'] ?>/archive">
            <?= \App\Helpers\CSRF::field() ?>
            <button class="btn btn-sm btn-outline-info" onclick="return confirm('Archive and finalise P&L for this batch?')">
                <i class="bi bi-archive me-1"></i>Archive
            </button>
        </form>
        <?php endif; ?>
    </div>
</div>

<!-- Batch KPIs -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-card primary">
            <div class="kpi-icon primary mb-2"><i class="bi bi-boxes"></i></div>
            <div class="kpi-value fs-3"><?= fmtN($batch['quantity_current']) ?></div>
            <div class="kpi-label">Current Stock</div>
            <div class="stock-bar mt-2">
                <div class="stock-bar-fill" style="width:<?= $pct ?>%;background:<?= $pct>40?'#16a34a':($pct>10?'#d97706':'#dc2626') ?>"></div>
            </div>
            <div class="text-muted mt-1" style="font-size:.72rem"><?= $pct ?>% remaining</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card success">
            <div class="kpi-icon success mb-2"><i class="bi bi-cart-check"></i></div>
            <div class="kpi-value fs-3"><?= fmtN($batch['quantity_sold']) ?></div>
            <div class="kpi-label">Units Sold</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card warning">
            <div class="kpi-icon warning mb-2"><i class="bi bi-cash"></i></div>
            <div class="kpi-value fs-4"><?= fmt($batch['total_revenue']) ?></div>
            <div class="kpi-label">Total Revenue</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card <?= (float)$batch['gross_profit'] >= 0 ? 'success' : 'danger' ?>">
            <div class="kpi-icon <?= (float)$batch['gross_profit'] >= 0 ? 'success' : 'danger' ?> mb-2"><i class="bi bi-graph-up"></i></div>
            <div class="kpi-value fs-4"><?= fmt($batch['gross_profit']) ?></div>
            <div class="kpi-label">Gross Profit</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <!-- Batch Info -->
    <div class="col-12 col-md-5">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-info-circle me-2 text-primary"></i>Batch Information</div>
            <div class="card-body">
                <table class="table table-sm small mb-0">
                    <tr><th class="text-muted fw-normal" width="45%">Batch Code</th><td><code><?= htmlspecialchars($batch['batch_code']) ?></code></td></tr>
                    <tr><th class="text-muted fw-normal">Product</th><td><?= htmlspecialchars($batch['product_name']) ?></td></tr>
                    <tr><th class="text-muted fw-normal">Received</th><td><?= fmtN($batch['quantity_received']) ?> units</td></tr>
                    <tr><th class="text-muted fw-normal">Acquisition Date</th><td><?= date('d/m/Y', strtotime($batch['acquisition_date'])) ?></td></tr>
                    <?php if ($batch['expiry_date']): ?>
                    <tr><th class="text-muted fw-normal">Expiry</th><td><?= date('d/m/Y', strtotime($batch['expiry_date'])) ?></td></tr>
                    <?php endif; ?>
                    <?php if (\App\Helpers\Auth::can('inventory.view_cost')): ?>
                    <tr><th class="text-muted fw-normal">Cost Price</th><td class="text-danger"><?= fmt($batch['cost_price']) ?></td></tr>
                    <?php endif; ?>
                    <tr><th class="text-muted fw-normal">Selling Price</th><td class="text-primary fw-semibold"><?= fmt($batch['selling_price']) ?></td></tr>
                    <tr><th class="text-muted fw-normal">Supplier</th><td><?= htmlspecialchars($batch['supplier_name'] ?: '—') ?></td></tr>
                    <tr><th class="text-muted fw-normal">Supplier Ref</th><td><?= htmlspecialchars($batch['supplier_ref'] ?: '—') ?></td></tr>
                    <tr><th class="text-muted fw-normal">Status</th>
                        <td><span class="badge status-<?= $batch['status'] ?>"><?= ucfirst($batch['status']) ?></span></td></tr>
                    <?php if ($batch['notes']): ?>
                    <tr><th class="text-muted fw-normal">Notes</th><td><?= htmlspecialchars($batch['notes']) ?></td></tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>

    <!-- Financial summary -->
    <div class="col-12 col-md-7">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-currency-dollar me-2 text-primary"></i>Financial Summary</div>
            <div class="card-body">
                <div class="row g-3 text-center">
                    <?php
                    $items = [
                        ['label'=>'Total Revenue',    'val'=>fmt($batch['total_revenue']),  'c'=>'primary'],
                        ['label'=>'Total COGS',       'val'=>fmt($batch['total_cost']),     'c'=>'secondary'],
                        ['label'=>'Gross Profit',     'val'=>fmt($batch['gross_profit']),   'c'=>(float)$batch['gross_profit']>=0?'success':'danger'],
                        ['label'=>'Loss (Damage)',    'val'=>fmt($batch['loss_amount']),    'c'=>'danger'],
                        ['label'=>'Net Profit',       'val'=>fmt($batch['net_profit']),     'c'=>(float)$batch['net_profit']>=0?'success':'danger'],
                        ['label'=>'Damaged Units',    'val'=>fmtN($batch['quantity_damaged']),'c'=>'warning'],
                    ];
                    foreach ($items as $i):
                    ?>
                    <div class="col-6 col-md-4 border-bottom border-end pb-3 pt-2">
                        <div class="fw-bold text-<?= $i['c'] ?> fs-5"><?= $i['val'] ?></div>
                        <div class="text-muted" style="font-size:.72rem"><?= $i['label'] ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php if ($batch['status'] === 'active' && \App\Helpers\Auth::can('inventory.view_cost')): ?>
                <div class="mt-3 p-3 bg-light rounded small">
                    <strong>Remaining Stock Value:</strong>
                    <?= fmt((float)$batch['quantity_current'] * (float)$batch['cost_price']) ?> (cost) &nbsp;/&nbsp;
                    <?= fmt((float)$batch['quantity_current'] * (float)$batch['selling_price']) ?> (retail)
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Movement History -->
<div class="card">
    <div class="card-header"><i class="bi bi-clock-history me-2 text-primary"></i>Stock Movement History</div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 small">
            <thead>
                <tr>
                    <th>Date & Time</th>
                    <th>Type</th>
                    <th class="text-end">Before</th>
                    <th class="text-end">Change</th>
                    <th class="text-end">After</th>
                    <th>Reference</th>
                    <th>By</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($movements)): ?>
                <tr><td colspan="8" class="text-center text-muted py-4">No movements recorded yet.</td></tr>
            <?php else: ?>
                <?php foreach ($movements as $m):
                    [$icon, $iconCol] = $mvtIcons[$m['movement_type']] ?? ['bi-arrow-right','text-muted'];
                ?>
                <tr>
                    <td class="text-nowrap"><?= date('d/m/Y H:i', strtotime($m['performed_at'])) ?></td>
                    <td>
                        <span class="d-flex align-items-center gap-1">
                            <i class="bi <?= $icon ?> <?= $iconCol ?>"></i>
                            <?= ucfirst(str_replace('_',' ',$m['movement_type'])) ?>
                        </span>
                    </td>
                    <td class="text-end"><?= fmtN($m['quantity_before']) ?></td>
                    <td class="text-end <?= (int)$m['quantity_change'] >= 0 ? 'text-success' : 'text-danger' ?> fw-semibold">
                        <?= (int)$m['quantity_change'] >= 0 ? '+' : '' ?><?= fmtN($m['quantity_change']) ?>
                    </td>
                    <td class="text-end fw-semibold"><?= fmtN($m['quantity_after']) ?></td>
                    <td>
                        <?php if ($m['reference_type']): ?>
                        <span class="badge text-bg-light"><?= htmlspecialchars(ucfirst($m['reference_type'])) ?> #<?= $m['reference_id'] ?></span>
                        <?php else: ?>—<?php endif; ?>
                    </td>
                    <td class="text-truncate" style="max-width:100px"><?= htmlspecialchars($m['by_name'] ?? 'System') ?></td>
                    <td class="text-muted text-truncate" style="max-width:120px"><?= htmlspecialchars($m['notes'] ?? '') ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

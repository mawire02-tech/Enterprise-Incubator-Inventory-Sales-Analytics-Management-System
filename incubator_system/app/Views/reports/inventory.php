<?php /* reports/inventory.php */
function fmt($v){return '$'.number_format((float)$v,2);}
function fmtN($v){return number_format((float)$v);}
?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center">
        <a href="<?= APP_URL ?>/reports" class="btn btn-sm btn-outline-secondary me-3"><i class="bi bi-arrow-left"></i></a>
        <div><h1 class="page-title">Inventory Report</h1>
        <p class="page-subtitle mb-0">Current stock levels and valuation</p></div>
    </div>
    <button onclick="window.location=`${APP_URL}/reports/export?type=inventory&from=2000-01-01&to=<?= date('Y-m-d') ?>&format=csv`" class="btn btn-sm btn-success">
        <i class="bi bi-download me-1"></i>Export
    </button>
</div>

<!-- Summary KPIs -->
<div class="row g-3 mb-4">
    <?php foreach ([
        ['Total Stock',    fmtN($invStats['total_stock']??0).' units',  'boxes',             'primary'],
        ['Stock Value',    fmt($invStats['stock_value_cost']??0),        'cash-stack',        'success'],
        ['Retail Value',   fmt($invStats['stock_value_retail']??0),      'tags',              'info'],
        ['Active Batches', fmtN($invStats['active_batches']??0),        'layers',            'warning'],
        ['Low Stock',      fmtN($invStats['low_stock_items']??0),       'exclamation-triangle','warning'],
        ['Out of Stock',   fmtN($invStats['out_of_stock_items']??0),    'x-circle',          'danger'],
    ] as [$l,$v,$i,$c]): ?>
    <div class="col-6 col-sm-4 col-md-2">
        <div class="kpi-card <?= $c ?>">
            <div class="kpi-icon <?= $c ?> mb-2"><i class="bi bi-<?= $i ?>"></i></div>
            <div class="kpi-value fs-5"><?= $v ?></div>
            <div class="kpi-label"><?= $l ?></div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Stock by Product -->
<div class="card mb-4">
    <div class="card-header"><i class="bi bi-boxes me-2 text-primary"></i>Stock Levels by Product</div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 small" id="invTable">
            <thead><tr>
                <th data-sort="sku">SKU</th>
                <th data-sort="name">Product</th>
                <th class="text-end" data-sort="total_stock">Stock Qty</th>
                <th class="text-end" data-sort="value_cost">Cost Value</th>
                <th class="text-end">Retail Value</th>
                <th class="text-end">Avg Cost</th>
                <th class="text-end">Avg Sell</th>
                <th>Alert</th>
            </tr></thead>
            <tbody>
            <?php foreach ($valuation as $p):
                $qty = (int)$p['total_qty'];
                $isLow = $qty > 0 && $qty <= 5;
                $isOut = $qty === 0;
            ?>
            <tr>
                <td><code class="small"><?= htmlspecialchars($p['sku']) ?></code></td>
                <td class="fw-medium"><?= htmlspecialchars($p['name']) ?></td>
                <td class="text-end"><?= fmtN($qty) ?></td>
                <td class="text-end fw-semibold"><?= fmt($p['total_value_cost']) ?></td>
                <td class="text-end"><?= fmt($p['total_value_retail']) ?></td>
                <td class="text-end text-muted"><?= fmt($p['avg_cost']) ?></td>
                <td class="text-end"><?= fmt($p['avg_sell']) ?></td>
                <td>
                    <?php if ($isOut): ?><span class="badge status-out">Out of Stock</span>
                    <?php elseif ($isLow): ?><span class="badge status-low">Low Stock</span>
                    <?php else: ?><span class="badge text-bg-success">OK</span><?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>document.addEventListener('DOMContentLoaded',()=>DataTable.init('invTable'));</script>

<?php /* reports/pnl.php */
function fmt($v){return '$'.number_format((float)$v,2);}
function fmtN($v){return number_format((float)$v);}
$marginPct = (float)($summary['total_revenue']??0)>0
    ? round((float)($summary['gross_profit']??0)/(float)($summary['total_revenue']??1)*100,1) : 0;
?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center">
        <a href="<?= APP_URL ?>/reports" class="btn btn-sm btn-outline-secondary me-3"><i class="bi bi-arrow-left"></i></a>
        <div>
            <h1 class="page-title">Profit & Loss</h1>
            <p class="page-subtitle mb-0"><?= date('d M Y',strtotime($from)) ?> – <?= date('d M Y',strtotime($to)) ?></p>
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= APP_URL ?>/reports/pnl?from=<?= date('Y-m-01') ?>&to=<?= date('Y-m-d') ?>" class="btn btn-sm btn-outline-secondary">This Month</a>
        <a href="<?= APP_URL ?>/reports/pnl?from=<?= date('Y-01-01') ?>&to=<?= date('Y-m-d') ?>" class="btn btn-sm btn-outline-secondary">This Year</a>
    </div>
</div>

<div class="card mb-4"><div class="card-body py-2">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-sm-3 col-md-2"><label class="form-label mb-1" style="font-size:.75rem">From</label>
            <input type="date" name="from" class="form-control form-control-sm" value="<?= $from ?>"></div>
        <div class="col-sm-3 col-md-2"><label class="form-label mb-1" style="font-size:.75rem">To</label>
            <input type="date" name="to" class="form-control form-control-sm" value="<?= $to ?>"></div>
        <div class="col-auto"><button class="btn btn-sm btn-primary">Apply</button></div>
    </form>
</div></div>

<!-- P&L Statement -->
<div class="row g-3 mb-4">
    <div class="col-12 col-md-5">
        <div class="card">
            <div class="card-header fw-bold"><i class="bi bi-file-text me-2 text-primary"></i>P&L Statement</div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0 small">
                    <tbody>
                    <tr class="table-light"><td colspan="2" class="fw-semibold text-uppercase small text-muted px-3">Income</td></tr>
                    <tr><td class="px-3">Total Revenue</td><td class="text-end px-3 fw-bold text-primary"><?= fmt($summary['total_revenue']??0) ?></td></tr>
                    <tr><td class="px-3 text-muted">Less: Discounts</td><td class="text-end px-3 text-danger">−<?= fmt($summary['total_discounts']??0) ?></td></tr>
                    <tr class="table-light"><td colspan="2" class="fw-semibold text-uppercase small text-muted px-3">Cost of Goods</td></tr>
                    <tr><td class="px-3 text-muted">Cost of Goods Sold (COGS)</td><td class="text-end px-3 text-danger">−<?= fmt($summary['total_cogs']??0) ?></td></tr>
                    <tr class="border-top"><td class="px-3 fw-bold">Gross Profit</td>
                        <td class="text-end px-3 fw-bold <?= (float)($summary['gross_profit']??0)>=0?'text-success':'text-danger' ?>"><?= fmt($summary['gross_profit']??0) ?></td></tr>
                    <tr><td class="px-3 text-muted ps-4">Gross Margin</td><td class="text-end px-3"><?= $marginPct ?>%</td></tr>
                    <tr class="table-light"><td colspan="2" class="fw-semibold text-uppercase small text-muted px-3">Other Costs</td></tr>
                    <tr><td class="px-3 text-muted">Returns</td><td class="text-end px-3 text-danger">−<?= fmt($summary['return_losses']??0) ?></td></tr>
                    <tr><td class="px-3 text-muted">Damage Losses</td><td class="text-end px-3 text-danger">−<?= fmt($summary['damage_losses']??0) ?></td></tr>
                    <tr class="table-success border-top"><td class="px-3 fw-bold">Net Profit</td>
                        <td class="text-end px-3 fw-bold <?= (float)($summary['net_profit']??0)>=0?'text-success':'text-danger' ?>"><?= fmt($summary['net_profit']??0) ?></td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Inventory Value -->
        <div class="card mt-3">
            <div class="card-header"><i class="bi bi-boxes me-2 text-primary"></i>Current Inventory</div>
            <div class="card-body text-center">
                <div class="fw-bold fs-4 text-primary"><?= fmt($summary['inventory_value']??0) ?></div>
                <div class="text-muted small">Total stock value at cost</div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-7">
        <!-- Monthly trend -->
        <div class="card mb-3">
            <div class="card-header"><i class="bi bi-graph-up me-2 text-primary"></i>12-Month Profit Trend</div>
            <div class="card-body">
                <div class="chart-container" style="height:200px">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Product margins -->
        <?php if (!empty($margins)): ?>
        <div class="card">
            <div class="card-header"><i class="bi bi-bar-chart-line me-2 text-primary"></i>Product Margin Analysis</div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 small">
                    <thead><tr><th>Product</th><th class="text-end">Units</th><th class="text-end">Revenue</th><th class="text-end">Profit</th><th class="text-end">Margin</th></tr></thead>
                    <tbody>
                    <?php foreach ($margins as $m): $mp=round((float)$m['margin_pct'],1); ?>
                    <tr>
                        <td class="text-truncate" style="max-width:130px"><?= htmlspecialchars($m['name']) ?></td>
                        <td class="text-end"><?= fmtN($m['qty_sold']) ?></td>
                        <td class="text-end"><?= fmt($m['revenue']) ?></td>
                        <td class="text-end <?= (float)$m['profit']>=0?'text-success':'text-danger' ?>"><?= fmt($m['profit']) ?></td>
                        <td class="text-end">
                            <span class="badge text-bg-<?= $mp>=30?'success':($mp>=15?'warning':'danger') ?>"><?= $mp ?>%</span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const monthly = <?= json_encode($monthly ?? []) ?>;
    if (monthly.length) {
        Charts.lineChart(
            document.getElementById('trendChart').getContext('2d'),
            monthly.map(r => r.month),
            [
                { label: 'Revenue',      data: monthly.map(r => parseFloat(r.revenue||0)) },
                { label: 'Gross Profit', data: monthly.map(r => parseFloat(r.gross_profit||0)) },
            ]
        );
    }
});
</script>

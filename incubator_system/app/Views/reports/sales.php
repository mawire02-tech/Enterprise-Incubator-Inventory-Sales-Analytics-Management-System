<?php /* reports/sales.php */
function fmt($v){return '$'.number_format((float)$v,2);}
function fmtN($v){return number_format((float)$v);}
?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center">
        <a href="<?= APP_URL ?>/reports" class="btn btn-sm btn-outline-secondary me-3"><i class="bi bi-arrow-left"></i></a>
        <div>
            <h1 class="page-title">Sales Report</h1>
            <p class="page-subtitle mb-0"><?= date('d M Y', strtotime($from)) ?> – <?= date('d M Y', strtotime($to)) ?></p>
        </div>
    </div>
    <button class="btn btn-sm btn-success" onclick="exportReport()">
        <i class="bi bi-download me-1"></i>Export CSV
    </button>
</div>

<!-- Filters -->
<div class="card mb-4"><div class="card-body py-2">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-sm-3 col-md-2">
            <label class="form-label mb-1" style="font-size:.75rem">From</label>
            <input type="date" name="from" class="form-control form-control-sm" value="<?= $from ?>">
        </div>
        <div class="col-sm-3 col-md-2">
            <label class="form-label mb-1" style="font-size:.75rem">To</label>
            <input type="date" name="to" class="form-control form-control-sm" value="<?= $to ?>">
        </div>
        <div class="col-sm-3 col-md-2">
            <label class="form-label mb-1" style="font-size:.75rem">Group By</label>
            <select name="group" class="form-select form-select-sm">
                <option value="day"   <?= $groupBy==='day'?'selected':'' ?>>Day</option>
                <option value="week"  <?= $groupBy==='week'?'selected':'' ?>>Week</option>
                <option value="month" <?= $groupBy==='month'?'selected':'' ?>>Month</option>
            </select>
        </div>
        <div class="col-auto">
            <button class="btn btn-sm btn-primary">Generate</button>
        </div>
    </form>
</div></div>

<!-- Summary KPIs -->
<div class="row g-3 mb-4">
    <?php
    $kpis = [
        ['Revenue',      fmt($summary['total_revenue']??0),     'cash-stack',  'primary'],
        ['COGS',         fmt($summary['total_cogs']??0),        'archive',     'secondary'],
        ['Gross Profit', fmt($summary['gross_profit']??0),      'graph-up',    'success'],
        ['Net Profit',   fmt($summary['net_profit']??0),        'award',       (float)($summary['net_profit']??0)>=0?'success':'danger'],
        ['Transactions', fmtN($summary['total_transactions']??0),'receipt',    'info'],
        ['Discounts',    fmt($summary['total_discounts']??0),   'tag',         'warning'],
    ];
    foreach ($kpis as [$l,$v,$i,$c]):
    ?>
    <div class="col-6 col-sm-4 col-md-2">
        <div class="kpi-card <?= $c ?>">
            <div class="kpi-icon <?= $c ?> mb-2"><i class="bi bi-<?= $i ?>"></i></div>
            <div class="kpi-value fs-5"><?= $v ?></div>
            <div class="kpi-label"><?= $l ?></div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Revenue Chart -->
<div class="row g-3 mb-4">
    <div class="col-12 col-md-8">
        <div class="card">
            <div class="card-header"><i class="bi bi-bar-chart me-2 text-primary"></i>Revenue by <?= ucfirst($groupBy) ?></div>
            <div class="card-body">
                <div class="chart-container" style="height:240px">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card">
            <div class="card-header"><i class="bi bi-pie-chart me-2 text-primary"></i>By Payment Method</div>
            <div class="card-body">
                <div class="chart-container" style="height:240px">
                    <canvas id="methodChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Grouped table -->
<div class="card mb-4">
    <div class="card-header"><i class="bi bi-table me-2 text-primary"></i>Sales by <?= ucfirst($groupBy) ?></div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 small" id="groupedTable">
            <thead><tr>
                <th data-sort="period">Period</th>
                <th class="text-end" data-sort="transactions">Transactions</th>
                <th class="text-end" data-sort="revenue">Revenue</th>
                <th class="text-end" data-sort="cost">COGS</th>
                <th class="text-end" data-sort="gross_profit">Gross Profit</th>
                <th class="text-end">Margin %</th>
                <th class="text-end" data-sort="discounts">Discounts</th>
            </tr></thead>
            <tbody>
            <?php if (empty($grouped)): ?>
                <tr><td colspan="7" class="text-center text-muted py-4">No data for this period.</td></tr>
            <?php else: ?>
                <?php foreach ($grouped as $row):
                    $margin = (float)$row['revenue']>0 ? (float)$row['gross_profit']/(float)$row['revenue']*100 : 0;
                ?>
                <tr>
                    <td class="fw-semibold"><?= htmlspecialchars($row['period']) ?></td>
                    <td class="text-end"><?= fmtN($row['transactions']) ?></td>
                    <td class="text-end fw-semibold"><?= fmt($row['revenue']) ?></td>
                    <td class="text-end text-muted"><?= fmt($row['cost']) ?></td>
                    <td class="text-end <?= (float)$row['gross_profit']>=0?'text-success':'text-danger' ?> fw-semibold"><?= fmt($row['gross_profit']) ?></td>
                    <td class="text-end">
                        <div class="d-flex align-items-center justify-content-end gap-2">
                            <span><?= round($margin,1) ?>%</span>
                            <div class="stock-bar" style="width:50px">
                                <div class="stock-bar-fill" style="width:<?= min(100,$margin) ?>%;background:<?= $margin>=30?'#16a34a':($margin>=15?'#d97706':'#dc2626') ?>"></div>
                            </div>
                        </div>
                    </td>
                    <td class="text-end text-muted"><?= fmt($row['discounts']) ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Best Sellers -->
<?php if (!empty($bestSellers)): ?>
<div class="row g-3">
    <div class="col-12 col-md-6">
        <div class="card">
            <div class="card-header"><i class="bi bi-trophy me-2 text-warning"></i>Best Selling Products</div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 small">
                    <thead><tr><th>Product</th><th class="text-end">Units</th><th class="text-end">Revenue</th><th class="text-end">Profit</th></tr></thead>
                    <tbody>
                    <?php foreach ($bestSellers as $bs): ?>
                    <tr>
                        <td class="text-truncate" style="max-width:150px"><?= htmlspecialchars($bs['name']) ?></td>
                        <td class="text-end"><?= fmtN($bs['total_qty']) ?></td>
                        <td class="text-end fw-semibold"><?= fmt($bs['total_revenue']) ?></td>
                        <td class="text-end text-success"><?= fmt($bs['total_profit']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php if (!empty($byMethod)): ?>
    <div class="col-12 col-md-6">
        <div class="card">
            <div class="card-header"><i class="bi bi-credit-card me-2 text-primary"></i>Revenue by Payment Method</div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 small">
                    <thead><tr><th>Method</th><th class="text-end">Transactions</th><th class="text-end">Revenue</th></tr></thead>
                    <tbody>
                    <?php foreach ($byMethod as $m): ?>
                    <tr>
                        <td class="fw-semibold"><?= ucfirst(str_replace('_',' ',$m['payment_method'])) ?></td>
                        <td class="text-end"><?= fmtN($m['transactions']) ?></td>
                        <td class="text-end fw-semibold"><?= fmt($m['revenue']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', () => {
    DataTable.init('groupedTable');

    // Revenue bar chart
    const grouped = <?= json_encode($grouped) ?>;
    if (grouped.length) {
        Charts.barChart(
            document.getElementById('salesChart').getContext('2d'),
            grouped.map(r => r.period),
            [
                { label: 'Revenue',      data: grouped.map(r => parseFloat(r.revenue||0)) },
                { label: 'Gross Profit', data: grouped.map(r => parseFloat(r.gross_profit||0)) },
            ]
        );
    }

    // Payment method doughnut
    const methods = <?= json_encode($byMethod) ?>;
    if (methods.length) {
        Charts.doughnut(
            document.getElementById('methodChart').getContext('2d'),
            methods.map(m => m.payment_method),
            methods.map(m => parseFloat(m.revenue||0))
        );
    }
});

function exportReport() {
    const from = '<?= $from ?>';
    const to   = '<?= $to ?>';
    window.location = `${APP_URL}/reports/export?type=sales&from=${from}&to=${to}&format=csv`;
}
</script>

<?php
$sym = '$';
function fmt($v, $sym='$') { return $sym . number_format((float)$v, 2); }
function fmtN($v) { return number_format((float)$v); }

$role           = \App\Helpers\Auth::role();
$canViewFinancials = $canViewFinancials ?? false;  // set by DashboardController
$canViewProfit     = $canViewProfit     ?? false;
$isSalesOfficer    = ($role === 'sales_officer');
$isStockClerk      = ($role === 'stock_clerk');
$isManager         = ($role === 'manager');
$isAdmin           = \App\Helpers\Auth::isAdmin();
?>

<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="page-title">Dashboard</h1>
        <p class="page-subtitle mb-0">
            <?= date('l, d F Y') ?> &nbsp;•&nbsp;
            <span class="pulse" style="display:inline-block;width:8px;height:8px;background:#16a34a;border-radius:50%;margin-right:4px"></span>
            <span class="text-success fw-medium" style="font-size:.8rem">Live</span>
        </p>
    </div>
    <div class="d-flex gap-2">
        <?php if (!$isStockClerk && !$isManager): ?>
        <a href="<?= APP_URL ?>/sales/create" class="btn btn-primary btn-sm">
            <i class="bi bi-cart-plus me-1"></i>New Sale
        </a>
        <?php endif; ?>
        <?php if (\App\Helpers\Auth::can('inventory.receive')): ?>
        <a href="<?= APP_URL ?>/inventory/batches/receive" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-box-arrow-in-down me-1"></i>Receive Stock
        </a>
        <?php endif; ?>
    </div>
</div>

<?php if ($isStockClerk): ?>
<!-- ═══════════════════════════════════════════════════════════
     STOCK CLERK DASHBOARD — Stock Alerts + Product Quantities
     No revenue, profit, COGS, or sales data shown
     ══════════════════════════════════════════════════════════ -->

<div class="row g-3 mb-4">
    <div class="col-6 col-sm-4 col-md-3">
        <div class="kpi-card primary fade-in">
            <div class="kpi-icon primary mb-2"><i class="bi bi-boxes"></i></div>
            <div class="kpi-value fs-4" id="kpi-stock"><?= fmtN($invStats['total_stock'] ?? 0) ?></div>
            <div class="kpi-label">Total Stock Units</div>
        </div>
    </div>
    <div class="col-6 col-sm-4 col-md-3">
        <div class="kpi-card warning fade-in">
            <div class="kpi-icon warning mb-2"><i class="bi bi-exclamation-triangle"></i></div>
            <div class="kpi-value fs-4 <?= ($invStats['low_stock_items'] ?? 0) > 0 ? 'text-warning' : '' ?>" id="kpi-low-stock">
                <?= fmtN($invStats['low_stock_items'] ?? 0) ?>
            </div>
            <div class="kpi-label">Low Stock</div>
        </div>
    </div>
    <div class="col-6 col-sm-4 col-md-3">
        <div class="kpi-card danger fade-in">
            <div class="kpi-icon danger mb-2"><i class="bi bi-x-circle"></i></div>
            <div class="kpi-value fs-4 <?= ($invStats['out_of_stock_items'] ?? 0) > 0 ? 'text-danger' : '' ?>" id="kpi-out">
                <?= fmtN($invStats['out_of_stock_items'] ?? 0) ?>
            </div>
            <div class="kpi-label">Out of Stock</div>
        </div>
    </div>
    <div class="col-6 col-sm-4 col-md-3">
        <div class="kpi-card fade-in" style="border-left:4px solid #7c3aed">
            <div class="kpi-icon mb-2" style="background:#f3e8ff;color:#7c3aed"><i class="bi bi-box-seam"></i></div>
            <div class="kpi-value fs-4"><?= fmtN($invStats['active_batches'] ?? 0) ?></div>
            <div class="kpi-label">Active Batches</div>
        </div>
    </div>
</div>

<!-- Stock Alerts + Product Quantities -->
<div class="row g-3 mb-4">

    <!-- Out of stock -->
    <?php if (!empty($outOfStockItems)): ?>
    <div class="col-12 col-md-6">
        <div class="card border-danger">
            <div class="card-header text-danger bg-danger bg-opacity-10">
                <i class="bi bi-x-circle me-2"></i>Out of Stock (<?= count($outOfStockItems) ?>)
            </div>
            <div class="table-responsive">
                <table class="table table-sm mb-0 small">
                    <thead><tr><th>Product</th><th>SKU</th><th class="text-center">Stock</th></tr></thead>
                    <tbody>
                    <?php foreach ($outOfStockItems as $p): ?>
                        <tr>
                            <td><?= htmlspecialchars($p['name']) ?></td>
                            <td><code><?= htmlspecialchars($p['sku'] ?? '—') ?></code></td>
                            <td class="text-center"><span class="badge bg-danger">0</span></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Low stock -->
    <?php if (!empty($lowStockItems)): ?>
    <div class="col-12 col-md-6">
        <div class="card border-warning">
            <div class="card-header text-warning bg-warning bg-opacity-10">
                <i class="bi bi-exclamation-triangle me-2"></i>Low Stock Alerts (<?= count($lowStockItems) ?>)
            </div>
            <div class="table-responsive">
                <table class="table table-sm mb-0 small">
                    <thead><tr><th>Product</th><th>SKU</th><th class="text-center">Qty</th><th class="text-center">Threshold</th></tr></thead>
                    <tbody>
                    <?php foreach ($lowStockItems as $p): ?>
                        <tr>
                            <td><?= htmlspecialchars($p['name']) ?></td>
                            <td><code><?= htmlspecialchars($p['sku'] ?? '—') ?></code></td>
                            <td class="text-center text-warning fw-semibold"><?= fmtN($p['total_stock']) ?></td>
                            <td class="text-center text-muted"><?= fmtN($p['low_stock_threshold']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if (empty($outOfStockItems) && empty($lowStockItems)): ?>
    <div class="col-12">
        <div class="alert alert-success"><i class="bi bi-check-circle me-2"></i>All stock levels are healthy.</div>
    </div>
    <?php endif; ?>

</div>

<!-- Product Stock Quantities Table -->
<div class="card mb-4">
    <div class="card-header">
        <i class="bi bi-list-ul me-2 text-primary"></i>Product Stock Quantities
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 small">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>SKU</th>
                    <th>Category</th>
                    <th class="text-center">In Stock</th>
                    <th class="text-center">Threshold</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($stockLevels)): ?>
                <tr><td colspan="6" class="text-center text-muted py-3">No products found.</td></tr>
            <?php else: ?>
                <?php foreach ($stockLevels as $p): ?>
                <?php
                    $qty = (int)$p['total_stock'];
                    $thr = (int)$p['low_stock_threshold'];
                    $statusClass = $qty === 0 ? 'danger' : ($qty <= $thr ? 'warning' : 'success');
                    $statusLabel = $qty === 0 ? 'Out of Stock' : ($qty <= $thr ? 'Low' : 'OK');
                ?>
                <tr>
                    <td><?= htmlspecialchars($p['name']) ?></td>
                    <td><code><?= htmlspecialchars($p['sku'] ?? '—') ?></code></td>
                    <td><?= htmlspecialchars($p['category'] ?? '—') ?></td>
                    <td class="text-center fw-semibold text-<?= $statusClass ?>"><?= fmtN($qty) ?></td>
                    <td class="text-center text-muted"><?= fmtN($thr) ?></td>
                    <td class="text-center">
                        <span class="badge bg-<?= $statusClass ?>"><?= $statusLabel ?></span>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Stock clerk: poll inventory KPIs only, no financial data
    LiveFeed.start(function(data) {
        if (data.inventory) {
            document.getElementById('kpi-stock')?.textContent !== undefined &&
                (document.getElementById('kpi-stock').textContent = parseInt(data.inventory.total_stock||0).toLocaleString());
            document.getElementById('kpi-low-stock')?.textContent !== undefined &&
                (document.getElementById('kpi-low-stock').textContent = data.inventory.low_stock_items||0);
            document.getElementById('kpi-out')?.textContent !== undefined &&
                (document.getElementById('kpi-out').textContent = data.inventory.out_of_stock_items||0);
        }
        if (data.unread !== undefined) Notifications.updateBadge(data.unread);
    });
});
</script>

<?php else: ?>
<!-- ═══════════════════════════════════════════════════════════
     SALES OFFICER / MANAGER / ADMIN DASHBOARD
     Sales Officer: no Gross Profit, Monthly Revenue, Stock Value,
                    Net Profit, COGS cards or ledger section
     Manager/Admin: full view
     ══════════════════════════════════════════════════════════ -->

<!-- ═══ KPI ROW 1 — SALES ═══════════════════════════════════ -->
<div class="row g-3 mb-4">
    <div class="col-6 col-sm-6 col-md-3">
        <div class="kpi-card primary fade-in">
            <div class="d-flex align-items-start justify-content-between mb-3">
                <div class="kpi-icon primary"><i class="bi bi-currency-dollar"></i></div>
                <span class="badge text-bg-light small">Today</span>
            </div>
            <?php if ($canViewFinancials): ?>
            <div class="kpi-value" id="kpi-today"><?= fmt($saleStats['today_revenue'] ?? 0) ?></div>
            <div class="kpi-label mt-1">Today's Revenue</div>
            <?php else: ?>
            <div class="kpi-value" id="kpi-sales-count-today"><?= fmtN($saleStats['today_sales_count'] ?? 0) ?></div>
            <div class="kpi-label mt-1">Sales Today</div>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($canViewFinancials): ?>
    <div class="col-6 col-sm-6 col-md-3">
        <div class="kpi-card success fade-in">
            <div class="d-flex align-items-start justify-content-between mb-3">
                <div class="kpi-icon success"><i class="bi bi-graph-up"></i></div>
                <span class="badge text-bg-light small">Month</span>
            </div>
            <div class="kpi-value" id="kpi-month"><?= fmt($saleStats['month_revenue'] ?? 0) ?></div>
            <div class="kpi-label mt-1">Monthly Revenue</div>
        </div>
    </div>
    <div class="col-6 col-sm-6 col-md-3">
        <div class="kpi-card warning fade-in">
            <div class="d-flex align-items-start justify-content-between mb-3">
                <div class="kpi-icon warning"><i class="bi bi-bar-chart"></i></div>
                <span class="badge text-bg-light small">Month</span>
            </div>
            <div class="kpi-value" id="kpi-profit"><?= fmt($saleStats['month_profit'] ?? 0) ?></div>
            <div class="kpi-label mt-1">Gross Profit</div>
        </div>
    </div>
    <?php else: ?>
    <!-- Sales Officer: replace revenue/profit cards with transaction counts -->
    <div class="col-6 col-sm-6 col-md-3">
        <div class="kpi-card success fade-in">
            <div class="d-flex align-items-start justify-content-between mb-3">
                <div class="kpi-icon success"><i class="bi bi-graph-up"></i></div>
                <span class="badge text-bg-light small">Month</span>
            </div>
            <div class="kpi-value" id="kpi-month-count"><?= fmtN($saleStats['month_sales_count'] ?? 0) ?></div>
            <div class="kpi-label mt-1">Sales This Month</div>
        </div>
    </div>
    <div class="col-6 col-sm-6 col-md-3">
        <div class="kpi-card info fade-in">
            <div class="d-flex align-items-start justify-content-between mb-3">
                <div class="kpi-icon info"><i class="bi bi-people"></i></div>
                <span class="badge text-bg-light small">Total</span>
            </div>
            <div class="kpi-value"><?= fmtN($totalCustomers ?? 0) ?></div>
            <div class="kpi-label mt-1">Customers</div>
        </div>
    </div>
    <?php endif; ?>

    <div class="col-6 col-sm-6 col-md-3">
        <div class="kpi-card info fade-in">
            <div class="d-flex align-items-start justify-content-between mb-3">
                <div class="kpi-icon info"><i class="bi bi-receipt"></i></div>
                <span class="badge text-bg-light small">Today</span>
            </div>
            <div class="kpi-value" id="kpi-sales-count"><?= fmtN($saleStats['today_sales_count'] ?? 0) ?></div>
            <div class="kpi-label mt-1">Sales Today</div>
        </div>
    </div>
</div>

<!-- ═══ KPI ROW 2 — INVENTORY ════════════════════════════════ -->
<div class="row g-3 mb-4">
    <div class="col-6 col-sm-4 col-md-2">
        <div class="kpi-card primary fade-in">
            <div class="kpi-icon primary mb-2"><i class="bi bi-boxes"></i></div>
            <div class="kpi-value fs-4" id="kpi-stock"><?= fmtN($invStats['total_stock'] ?? 0) ?></div>
            <div class="kpi-label">Total Stock</div>
        </div>
    </div>

    <?php if ($canViewFinancials): ?>
    <!-- Stock Value: admin & manager only -->
    <div class="col-6 col-sm-4 col-md-2">
        <div class="kpi-card success fade-in">
            <div class="kpi-icon success mb-2"><i class="bi bi-cash-stack"></i></div>
            <div class="kpi-value fs-4" id="kpi-inv-value"><?= fmt($invStats['stock_value_cost'] ?? 0) ?></div>
            <div class="kpi-label">Stock Value</div>
        </div>
    </div>
    <?php endif; ?>

    <div class="col-6 col-sm-4 col-md-2">
        <div class="kpi-card warning fade-in">
            <div class="kpi-icon warning mb-2"><i class="bi bi-exclamation-triangle"></i></div>
            <div class="kpi-value fs-4 <?= ($invStats['low_stock_items'] ?? 0) > 0 ? 'text-warning' : '' ?>" id="kpi-low-stock">
                <?= fmtN($invStats['low_stock_items'] ?? 0) ?>
            </div>
            <div class="kpi-label">Low Stock</div>
        </div>
    </div>
    <div class="col-6 col-sm-4 col-md-2">
        <div class="kpi-card danger fade-in">
            <div class="kpi-icon danger mb-2"><i class="bi bi-x-circle"></i></div>
            <div class="kpi-value fs-4 <?= ($invStats['out_of_stock_items'] ?? 0) > 0 ? 'text-danger' : '' ?>" id="kpi-out">
                <?= fmtN($invStats['out_of_stock_items'] ?? 0) ?>
            </div>
            <div class="kpi-label">Out of Stock</div>
        </div>
    </div>
    <div class="col-6 col-sm-4 col-md-2">
        <div class="kpi-card info fade-in">
            <div class="kpi-icon info mb-2"><i class="bi bi-people"></i></div>
            <div class="kpi-value fs-4"><?= fmtN($totalCustomers ?? 0) ?></div>
            <div class="kpi-label">Customers</div>
        </div>
    </div>
    <div class="col-6 col-sm-4 col-md-2">
        <div class="kpi-card fade-in" style="border-left:4px solid #7c3aed">
            <div class="kpi-icon mb-2" style="background:#f3e8ff;color:#7c3aed"><i class="bi bi-box-seam"></i></div>
            <div class="kpi-value fs-4"><?= fmtN($invStats['active_batches'] ?? 0) ?></div>
            <div class="kpi-label">Active Batches</div>
        </div>
    </div>
</div>

<!-- ═══ CHARTS ROW — financial charts for manager/admin only ═ -->
<?php if ($canViewFinancials): ?>
<div class="row g-3 mb-4">
    <div class="col-12 col-md-8">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span><i class="bi bi-graph-up me-2 text-primary"></i>Revenue & Profit Trend</span>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-secondary active" onclick="loadRevenueChart('revenue',this)">Revenue</button>
                    <button class="btn btn-outline-secondary" onclick="loadRevenueChart('daily',this)">Daily</button>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-container" style="height:220px">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card h-100">
            <div class="card-header">
                <i class="bi bi-pie-chart me-2 text-primary"></i>Revenue by Payment
            </div>
            <div class="card-body">
                <div class="chart-container" style="height:220px">
                    <canvas id="paymentChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ═══ LOWER ROW ════════════════════════════════════════════ -->
<div class="row g-3 mb-4">

    <!-- Recent Sales -->
    <div class="col-12 col-lg-7">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span><i class="bi bi-receipt me-2 text-primary"></i>Recent Sales</span>
                <a href="<?= APP_URL ?>/sales" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 small">
                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <?php if ($canViewProfit): ?>
                            <th>Profit</th>
                            <?php endif; ?>
                            <th>Method</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($recentSales)): ?>
                        <tr><td colspan="<?= $canViewProfit ? 6 : 5 ?>" class="text-center text-muted py-3">No sales yet</td></tr>
                    <?php else: ?>
                        <?php foreach ($recentSales as $s): ?>
                        <tr onclick="window.location='<?= APP_URL ?>/sales/<?= $s['id'] ?>'" style="cursor:pointer">
                            <td><code class="small"><?= htmlspecialchars($s['invoice_number']) ?></code></td>
                            <td class="text-truncate" style="max-width:130px">
                                <?= htmlspecialchars($s['customer_full_name'] ?? $s['customer_name'] ?? '—') ?>
                            </td>
                            <td class="fw-semibold"><?= fmt($s['total_amount']) ?></td>
                            <?php if ($canViewProfit): ?>
                            <td class="text-success"><?= fmt($s['gross_profit'] ?? 0) ?></td>
                            <?php endif; ?>
                            <td><span class="badge text-bg-light"><?= ucfirst($s['payment_method']) ?></span></td>
                            <td>
                                <span class="badge status-<?= $s['status'] ?>"><?= ucfirst($s['status']) ?></span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Best Sellers + Stock Alerts -->
    <div class="col-12 col-lg-5">
        <?php if (!empty($bestSellers)): ?>
        <div class="card mb-3">
            <div class="card-header">
                <i class="bi bi-trophy me-2 text-warning"></i>Best Sellers (This Month)
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                <?php foreach ($bestSellers as $i => $bs): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center py-2 small">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge text-bg-<?= $i === 0 ? 'warning' : ($i === 1 ? 'secondary' : 'light text-dark') ?>"><?= $i+1 ?></span>
                            <span class="text-truncate" style="max-width:150px"><?= htmlspecialchars($bs['name']) ?></span>
                        </div>
                        <div class="text-end">
                            <?php if ($canViewProfit): ?>
                            <div class="fw-semibold"><?= fmt($bs['total_revenue']) ?></div>
                            <?php endif; ?>
                            <div class="text-muted" style="font-size:.72rem"><?= fmtN($bs['total_qty']) ?> units</div>
                        </div>
                    </li>
                <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <?php endif; ?>

        <!-- Stock Alerts -->
        <?php if (!empty($lowStockItems) || !empty($outOfStockItems)): ?>
        <div class="card border-warning">
            <div class="card-header text-warning bg-warning bg-opacity-10">
                <i class="bi bi-exclamation-triangle me-2"></i>Stock Alerts
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                <?php foreach ($outOfStockItems as $p): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center py-2 small">
                        <span class="text-truncate"><?= htmlspecialchars($p['name']) ?></span>
                        <span class="badge status-out">Out of Stock</span>
                    </li>
                <?php endforeach; ?>
                <?php foreach ($lowStockItems as $p): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center py-2 small">
                        <span class="text-truncate"><?= htmlspecialchars($p['name']) ?></span>
                        <span class="badge status-low"><?= fmtN($p['total_stock']) ?> left</span>
                    </li>
                <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- ═══ TODAY'S LEDGER — manager/admin only ══════════════════ -->
<?php if ($canViewFinancials && $todayLedger): ?>
<div class="card mb-4">
    <div class="card-header">
        <i class="bi bi-journal-text me-2 text-primary"></i>Today's Ledger Summary
        — <?= date('d M Y') ?>
    </div>
    <div class="row g-0 p-3">
        <?php
        $ledgerItems = [
            ['label'=>'Transactions', 'val'=>fmtN($todayLedger['total_transactions']), 'icon'=>'receipt',  'col'=>'primary'],
            ['label'=>'Units Sold',   'val'=>fmtN($todayLedger['total_units_sold']),   'icon'=>'box',      'col'=>'info'],
            ['label'=>'Revenue',      'val'=>fmt($todayLedger['total_revenue']),        'icon'=>'cash',     'col'=>'success'],
            ['label'=>'COGS',         'val'=>fmt($todayLedger['total_inventory_cost']), 'icon'=>'archive',  'col'=>'secondary'],
            ['label'=>'Gross Profit', 'val'=>fmt($todayLedger['gross_profit']),         'icon'=>'graph-up', 'col'=>'warning'],
            ['label'=>'Net Profit',   'val'=>fmt($todayLedger['net_profit']),           'icon'=>'award',    'col'=>(float)$todayLedger['net_profit']>=0?'success':'danger'],
        ];
        foreach ($ledgerItems as $li):
        ?>
        <div class="col-6 col-md-4 col-lg-2 p-2 text-center border-end border-bottom">
            <i class="bi bi-<?= $li['icon'] ?> text-<?= $li['col'] ?> mb-1 d-block fs-5"></i>
            <div class="fw-bold"><?= $li['val'] ?></div>
            <div class="text-muted" style="font-size:.72rem"><?= $li['label'] ?></div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<script>
let revenueChart = null;
let paymentChart = null;

<?php if ($canViewFinancials): ?>
async function loadRevenueChart(type, btn) {
    document.querySelectorAll('.btn-group .btn').forEach(b => b.classList.remove('active'));
    btn?.classList.add('active');
    try {
        const res = await App.fetch(`${APP_URL}/dashboard/chart-data?type=${type}`);
        const raw = res.data;
        const labels  = raw.map(r => r.month || r.ledger_date || r.period);
        const revenue = raw.map(r => parseFloat(r.revenue || r.total_revenue || 0));
        const profit  = raw.map(r => parseFloat(r.gross_profit || 0));
        if (revenueChart) revenueChart.destroy();
        const ctx = document.getElementById('revenueChart').getContext('2d');
        revenueChart = Charts.lineChart(ctx, labels, [
            { label: 'Revenue',      data: revenue },
            { label: 'Gross Profit', data: profit  },
        ]);
    } catch(e) {}
}

async function loadPaymentChart() {
    try {
        const res = await App.fetch(`${APP_URL}/dashboard/chart-data?type=payment_methods`);
        const raw = res.data;
        const ctx = document.getElementById('paymentChart').getContext('2d');
        paymentChart = Charts.doughnut(
            ctx,
            raw.map(r => r.payment_method),
            raw.map(r => parseFloat(r.revenue))
        );
    } catch(e) {}
}
<?php endif; ?>

function updateKPIs(data) {
    if (data.sales) {
        <?php if ($canViewFinancials): ?>
        document.getElementById('kpi-today') && (document.getElementById('kpi-today').textContent = '$' + parseFloat(data.sales.today_revenue||0).toFixed(2));
        document.getElementById('kpi-month') && (document.getElementById('kpi-month').textContent = '$' + parseFloat(data.sales.month_revenue||0).toFixed(2));
        document.getElementById('kpi-profit') && (document.getElementById('kpi-profit').textContent = '$' + parseFloat(data.sales.month_profit||0).toFixed(2));
        <?php else: ?>
        document.getElementById('kpi-month-count') && (document.getElementById('kpi-month-count').textContent = parseInt(data.sales.month_sales_count||0).toLocaleString());
        <?php endif; ?>
        document.getElementById('kpi-sales-count') && (document.getElementById('kpi-sales-count').textContent = data.sales.today_sales_count||0);
    }
    if (data.inventory) {
        document.getElementById('kpi-stock') && (document.getElementById('kpi-stock').textContent = parseInt(data.inventory.total_stock||0).toLocaleString());
        <?php if ($canViewFinancials): ?>
        document.getElementById('kpi-inv-value') && (document.getElementById('kpi-inv-value').textContent = '$' + parseFloat(data.inventory.stock_value_cost||0).toFixed(2));
        <?php endif; ?>
        document.getElementById('kpi-low-stock') && (document.getElementById('kpi-low-stock').textContent = data.inventory.low_stock_items||0);
        document.getElementById('kpi-out') && (document.getElementById('kpi-out').textContent = data.inventory.out_of_stock_items||0);
    }
    if (data.unread !== undefined) Notifications.updateBadge(data.unread);
}

document.addEventListener('DOMContentLoaded', () => {
    <?php if ($canViewFinancials): ?>
    loadRevenueChart('revenue');
    loadPaymentChart();
    <?php endif; ?>
    LiveFeed.start(updateKPIs);
});
</script>

<?php endif; // end stock_clerk / everyone-else split ?>

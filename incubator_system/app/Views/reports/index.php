<?php /* reports/index.php */ ?>
<div class="mb-4">
    <h1 class="page-title">Reports</h1>
    <p class="page-subtitle mb-0">Generate insights from your business data</p>
</div>

<div class="row g-3">
    <?php
    $reports = [
        ['Sales Report',      '/reports/sales',     'receipt',        'primary',   'Daily, weekly, monthly sales analysis with payment breakdowns'],
        ['Inventory Report',  '/reports/inventory', 'box-seam',       'success',   'Stock levels, valuation and batch performance summary'],
        ['Profit & Loss',     '/reports/pnl',       'graph-up-arrow', 'warning',   'COGS, gross profit, net profit, margins and trend analysis'],
        ['Daily Ledger',      '/reports/ledger',    'journal-text',   'info',      'Day-by-day transaction totals and cash flow summary'],
    ];
    foreach ($reports as [$title,$path,$icon,$col,$desc]):
    ?>
    <div class="col-12 col-sm-6 col-lg-3">
        <a href="<?= APP_URL . $path ?>" class="text-decoration-none">
            <div class="card h-100 card-hover">
                <div class="card-body text-center py-4">
                    <div class="kpi-icon <?= $col ?> mx-auto mb-3" style="width:56px;height:56px;font-size:1.6rem">
                        <i class="bi bi-<?= $icon ?>"></i>
                    </div>
                    <div class="fw-bold mb-1"><?= $title ?></div>
                    <p class="text-muted small mb-0"><?= $desc ?></p>
                </div>
                <div class="card-footer text-center bg-<?= $col ?> bg-opacity-10">
                    <span class="small text-<?= $col ?> fw-semibold">Open Report →</span>
                </div>
            </div>
        </a>
    </div>
    <?php endforeach; ?>
</div>

<div class="row g-3 mt-2">
    <div class="col-12">
        <div class="card">
            <div class="card-header"><i class="bi bi-download me-2 text-primary"></i>Quick Export</div>
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-sm-3">
                        <label class="form-label">Report Type</label>
                        <select id="exportType" class="form-select form-select-sm">
                            <option value="sales">Sales</option>
                            <option value="inventory">Inventory</option>
                            <option value="customers">Customers</option>
                            <option value="ledger">Ledger</option>
                            <option value="movements">Stock Movements</option>
                        </select>
                    </div>
                    <div class="col-sm-2">
                        <label class="form-label">From</label>
                        <input type="date" id="exportFrom" class="form-control form-control-sm" value="<?= date('Y-m-01') ?>">
                    </div>
                    <div class="col-sm-2">
                        <label class="form-label">To</label>
                        <input type="date" id="exportTo" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="col-sm-2">
                        <label class="form-label">Format</label>
                        <select id="exportFormat" class="form-select form-select-sm">
                            <option value="csv">CSV</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-sm btn-success" onclick="doExport()">
                            <i class="bi bi-download me-1"></i>Export
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>.card-hover:hover{transform:translateY(-3px);box-shadow:0 8px 24px rgba(0,0,0,.12);}</style>
<script>
async function doExport() {
    const type   = document.getElementById('exportType').value;
    const from   = document.getElementById('exportFrom').value;
    const to     = document.getElementById('exportTo').value;
    const format = document.getElementById('exportFormat').value;
    try {
        const res = await App.fetch(`${APP_URL}/reports/export?type=${type}&from=${from}&to=${to}&format=${format}`);
        window.location = res.data.url;
        App.toast('Export ready — downloading…','success');
    } catch(e) { App.toast(e.message,'danger'); }
}
</script>

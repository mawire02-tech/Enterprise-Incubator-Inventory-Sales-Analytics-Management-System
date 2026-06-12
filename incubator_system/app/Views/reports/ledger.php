<?php /* reports/ledger.php */
function fmt($v){return '$'.number_format((float)$v,2);}
function fmtN($v){return number_format((float)$v);}
?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center">
        <a href="<?= APP_URL ?>/reports" class="btn btn-sm btn-outline-secondary me-3"><i class="bi bi-arrow-left"></i></a>
        <div><h1 class="page-title">Daily Sales Ledger</h1>
        <p class="page-subtitle mb-0"><?= date('d M Y',strtotime($from)) ?> – <?= date('d M Y',strtotime($to)) ?></p></div>
    </div>
    <button onclick="window.location=`${APP_URL}/reports/export?type=ledger&from=<?= $from ?>&to=<?= $to ?>&format=csv`" class="btn btn-sm btn-success">
        <i class="bi bi-download me-1"></i>Export
    </button>
</div>

<div class="card mb-3"><div class="card-body py-2">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-sm-3 col-md-2"><label class="form-label mb-1" style="font-size:.75rem">From</label>
            <input type="date" name="from" class="form-control form-control-sm" value="<?= $from ?>"></div>
        <div class="col-sm-3 col-md-2"><label class="form-label mb-1" style="font-size:.75rem">To</label>
            <input type="date" name="to" class="form-control form-control-sm" value="<?= $to ?>"></div>
        <div class="col-auto"><button class="btn btn-sm btn-primary">Filter</button></div>
    </form>
</div></div>

<!-- Totals -->
<?php if ($totals): ?>
<div class="row g-2 mb-4">
    <?php foreach ([
        ['Revenue',fmtN($totals['units']??0).' units / '.fmt($totals['revenue']??0),'cash','primary'],
        ['COGS',fmt($totals['cost']??0),'archive','secondary'],
        ['Gross Profit',fmt($totals['gross']??0),'graph-up','success'],
        ['Net Profit',fmt($totals['net']??0),'award',(float)($totals['net']??0)>=0?'success':'danger'],
    ] as [$l,$v,$i,$c]): ?>
    <div class="col-6 col-md-3">
        <div class="kpi-card <?= $c ?>">
            <div class="kpi-icon <?= $c ?> mb-2"><i class="bi bi-<?= $i ?>"></i></div>
            <div class="kpi-value fs-5"><?= $v ?></div>
            <div class="kpi-label"><?= $l ?></div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 small" id="ledgerTable">
            <thead><tr>
                <th data-sort="ledger_date">Date</th>
                <th class="text-end" data-sort="total_transactions">Txns</th>
                <th class="text-end" data-sort="total_units_sold">Units</th>
                <th class="text-end" data-sort="total_revenue">Revenue</th>
                <th class="text-end">COGS</th>
                <th class="text-end">Gross Profit</th>
                <th class="text-end">Returns</th>
                <th class="text-end">Net Profit</th>
                <th class="text-end">Cash</th>
                <th class="text-end">EcoCash</th>
            </tr></thead>
            <tbody>
            <?php if (empty($rows)): ?>
                <tr><td colspan="10" class="text-center text-muted py-5">No ledger data for this period.</td></tr>
            <?php else: ?>
                <?php foreach ($rows as $r): ?>
                <tr>
                    <td class="fw-semibold"><?= date('d/m/Y D', strtotime($r['ledger_date'])) ?></td>
                    <td class="text-end"><?= fmtN($r['total_transactions']) ?></td>
                    <td class="text-end"><?= fmtN($r['total_units_sold']) ?></td>
                    <td class="text-end fw-semibold"><?= fmt($r['total_revenue']) ?></td>
                    <td class="text-end text-muted"><?= fmt($r['total_inventory_cost']) ?></td>
                    <td class="text-end <?= (float)$r['gross_profit']>=0?'text-success':'text-danger' ?>"><?= fmt($r['gross_profit']) ?></td>
                    <td class="text-end text-danger"><?= (float)$r['total_returns']>0?fmt($r['total_returns']):'—' ?></td>
                    <td class="text-end fw-semibold <?= (float)$r['net_profit']>=0?'text-success':'text-danger' ?>"><?= fmt($r['net_profit']) ?></td>
                    <td class="text-end"><?= fmt($r['cash_sales']) ?></td>
                    <td class="text-end"><?= fmt($r['ecocash_sales']) ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<script>document.addEventListener('DOMContentLoaded',()=>DataTable.init('ledgerTable'));</script>

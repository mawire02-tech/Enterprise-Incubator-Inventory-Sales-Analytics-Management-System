<?php /* sales/index.php */
function fmt($v){return '$'.number_format((float)$v,2);}
$statusBadge = ['completed'=>'success','returned'=>'warning','voided'=>'danger'];
$payBadge    = ['paid'=>'success','partial'=>'warning','unpaid'=>'danger','refunded'=>'info'];
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="page-title">Sales</h1>
        <p class="page-subtitle mb-0">Browse, search and manage transactions</p>
    </div>
    <?php if (\App\Helpers\Auth::can('sales.create')): ?>
    <a href="<?= APP_URL ?>/sales/create" class="btn btn-primary btn-sm">
        <i class="bi bi-cart-plus me-1"></i>New Sale
    </a>
    <?php endif; ?>
</div>

<!-- Filters -->
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-sm-3 col-md-2">
                <label class="form-label mb-1" style="font-size:.75rem">From</label>
                <input type="date" name="from" class="form-control form-control-sm" value="<?= htmlspecialchars($from) ?>">
            </div>
            <div class="col-sm-3 col-md-2">
                <label class="form-label mb-1" style="font-size:.75rem">To</label>
                <input type="date" name="to" class="form-control form-control-sm" value="<?= htmlspecialchars($to) ?>">
            </div>
            <div class="col-sm-3 col-md-2">
                <label class="form-label mb-1" style="font-size:.75rem">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All</option>
                    <option value="completed" <?= $status==='completed'?'selected':'' ?>>Completed</option>
                    <option value="returned"  <?= $status==='returned'?'selected':'' ?>>Returned</option>
                    <option value="voided"    <?= $status==='voided'?'selected':'' ?>>Voided</option>
                </select>
            </div>
            <div class="col-sm-6 col-md-3">
                <label class="form-label mb-1" style="font-size:.75rem">Search</label>
                <input type="text" name="q" class="form-control form-control-sm"
                       placeholder="Invoice, customer…" value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-primary"><i class="bi bi-search me-1"></i>Filter</button>
                <a href="<?= APP_URL ?>/sales" class="btn btn-sm btn-outline-secondary ms-1">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Period summary -->
<?php if ($totals): ?>
<div class="row g-2 mb-3">
    <?php
    $items = [
        ['Transactions', number_format($totals['count']??0),         'receipt',    'secondary'],
        ['Revenue',      fmt($totals['total_revenue']??0),           'cash-stack', 'primary'],
        ['Gross Profit', fmt($totals['total_profit']??0),            'graph-up',   'success'],
    ];
    foreach ($items as [$l,$v,$i,$c]):
    ?>
    <div class="col-4">
        <div class="kpi-card <?= $c ?> p-3 text-center">
            <div class="kpi-value fs-5"><?= $v ?></div>
            <div class="kpi-label"><?= $l ?></div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 small" id="salesTable">
            <thead><tr>
                <th data-sort="invoice_number">Invoice</th>
                <th data-sort="sale_date">Date</th>
                <th>Customer</th>
                <th class="text-end" data-sort="total_amount">Total</th>
                <?php if (\App\Helpers\Auth::can('sales.view_profit')): ?>
                <th class="text-end">Profit</th>
                <?php endif; ?>
                <th>Payment</th>
                <th>Status</th>
                <th>Served By</th>
                <th>Actions</th>
            </tr></thead>
            <tbody>
            <?php if (empty($list)): ?>
                <tr><td colspan="9" class="text-center py-5 text-muted">
                    <i class="bi bi-receipt d-block fs-2 mb-2 opacity-25"></i>No sales found for this period.
                </td></tr>
            <?php else: ?>
                <?php foreach ($list as $s): ?>
                <tr>
                    <td><a href="<?= APP_URL ?>/sales/<?= $s['id'] ?>" class="text-decoration-none">
                        <code><?= htmlspecialchars($s['invoice_number']) ?></code>
                    </a></td>
                    <td><?= date('d/m/Y', strtotime($s['sale_date'])) ?></td>
                    <td class="text-truncate" style="max-width:130px">
                        <?= htmlspecialchars($s['customer_full_name'] ?? $s['customer_name'] ?? 'Walk-in') ?>
                    </td>
                    <td class="text-end fw-semibold"><?= fmt($s['total_amount']) ?></td>
                    <?php if (\App\Helpers\Auth::can('sales.view_profit')): ?>
                    <td class="text-end <?= (float)$s['gross_profit']>=0?'text-success':'text-danger' ?>">
                        <?= fmt($s['gross_profit']) ?>
                    </td>
                    <?php endif; ?>
                    <td>
                        <div class="small"><?= ucfirst(str_replace('_',' ',$s['payment_method'])) ?></div>
                        <span class="badge <?= 'text-bg-'.($payBadge[$s['payment_status']]??'secondary') ?>" style="font-size:.68em">
                            <?= ucfirst($s['payment_status']) ?>
                        </span>
                    </td>
                    <td><span class="badge status-<?= $s['status'] ?>"><?= ucfirst($s['status']) ?></span></td>
                    <td class="text-truncate" style="max-width:100px"><?= htmlspecialchars($s['served_by_name'] ?? '—') ?></td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="<?= APP_URL ?>/sales/<?= $s['id'] ?>" class="btn btn-xs btn-outline-secondary" title="View"><i class="bi bi-eye"></i></a>
                            <a href="<?= APP_URL ?>/sales/<?= $s['id'] ?>/print" target="_blank" class="btn btn-xs btn-outline-secondary" title="Print"><i class="bi bi-printer"></i></a>
                            <?php if ($s['status']==='completed' && \App\Helpers\Auth::can('sales.return')): ?>
                            <a href="<?= APP_URL ?>/sales/<?= $s['id'] ?>/return" class="btn btn-xs btn-outline-warning" title="Return"><i class="bi bi-arrow-return-left"></i></a>
                            <?php endif; ?>
                            <?php if ($s['status']==='completed' && \App\Helpers\Auth::can('sales.void')): ?>
                            <button class="btn btn-xs btn-outline-danger" onclick="voidSale(<?= $s['id'] ?>,'<?= htmlspecialchars($s['invoice_number']) ?>')" title="Void"><i class="bi bi-x-circle"></i></button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($paginate['pages'] > 1): ?>
    <div class="card-footer d-flex align-items-center justify-content-between small text-muted">
        <span>Showing <?= count($list) ?> of <?= number_format($paginate['total']) ?> sales</span>
        <nav><ul class="pagination pagination-sm mb-0">
            <?php for ($i = 1; $i <= $paginate['pages']; $i++): ?>
            <li class="page-item <?= $paginate['page']==$i?'active':'' ?>">
                <a class="page-link" href="?<?= http_build_query(array_merge($_GET,['page'=>$i])) ?>"><?= $i ?></a>
            </li>
            <?php endfor; ?>
        </ul></nav>
    </div>
    <?php endif; ?>
</div>

<!-- Void modal -->
<div class="modal fade" id="voidModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header border-0"><h6 class="modal-title text-danger"><i class="bi bi-x-circle me-2"></i>Void Sale</h6></div>
            <div class="modal-body">
                <p class="small text-muted mb-2">Voiding <strong id="voidInvoice"></strong> will reverse all stock deductions.</p>
                <input type="text" id="voidReason" class="form-control form-control-sm" placeholder="Reason for voiding *" required>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-sm btn-danger" id="voidConfirmBtn">Void Sale</button>
            </div>
        </div>
    </div>
</div>

<style>.btn-xs{padding:.2rem .45rem;font-size:.75rem;}</style>
<script>
let pendingVoidId = null;

function voidSale(id, invoice) {
    pendingVoidId = id;
    document.getElementById('voidInvoice').textContent = invoice;
    document.getElementById('voidReason').value = '';
    new bootstrap.Modal(document.getElementById('voidModal')).show();
}

document.getElementById('voidConfirmBtn')?.addEventListener('click', async () => {
    const reason = document.getElementById('voidReason').value.trim();
    if (!reason) { App.toast('Please enter a reason.','warning'); return; }
    try {
        await App.fetch(`${APP_URL}/sales/${pendingVoidId}/void`, {
            method:'POST', body:{ reason, _csrf_token: CSRF_TOKEN }
        });
        App.toast('Sale voided.','success');
        setTimeout(()=>location.reload(), 800);
    } catch(e) { App.toast(e.message,'danger'); }
});

document.addEventListener('DOMContentLoaded', () => DataTable.init('salesTable'));
</script>

<?php /* sales/show.php */
function fmt($v){return '$'.number_format((float)$v,2);}
$statusBadge=['completed'=>'success','returned'=>'warning','voided'=>'danger'];
$payBadge=['paid'=>'success','partial'=>'warning','unpaid'=>'danger','refunded'=>'info'];
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center">
        <a href="<?= APP_URL ?>/sales" class="btn btn-sm btn-outline-secondary me-3"><i class="bi bi-arrow-left"></i></a>
        <div>
            <h1 class="page-title">Invoice <?= htmlspecialchars($sale['invoice_number']) ?></h1>
            <p class="page-subtitle mb-0"><?= date('d F Y', strtotime($sale['sale_date'])) ?></p>
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= APP_URL ?>/sales/<?= $sale['id'] ?>/print" target="_blank" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-printer me-1"></i>Print
        </a>
        <?php if ($sale['status']==='completed' && \App\Helpers\Auth::can('sales.return')): ?>
        <a href="<?= APP_URL ?>/sales/<?= $sale['id'] ?>/return" class="btn btn-sm btn-outline-warning">
            <i class="bi bi-arrow-return-left me-1"></i>Return
        </a>
        <?php endif; ?>
        <?php if ($sale['status']==='completed' && \App\Helpers\Auth::can('sales.void')): ?>
        <button class="btn btn-sm btn-outline-danger" onclick="voidThis()">
            <i class="bi bi-x-circle me-1"></i>Void
        </button>
        <?php endif; ?>
    </div>
</div>

<div class="row g-3">
    <!-- Invoice card -->
    <div class="col-12 col-lg-8">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span><i class="bi bi-receipt me-2 text-primary"></i>Invoice Details</span>
                <div>
                    <span class="badge status-<?= $sale['status'] ?> me-1"><?= ucfirst($sale['status']) ?></span>
                    <span class="badge text-bg-<?= $payBadge[$sale['payment_status']] ?>"><?= ucfirst($sale['payment_status']) ?></span>
                </div>
            </div>
            <div class="card-body">
                <!-- Items table -->
                <div class="table-responsive mb-3">
                    <table class="table table-sm small mb-0">
                        <thead><tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>Batch</th>
                            <th class="text-center">Qty</th>
                            <th class="text-end">Unit Price</th>
                            <?php if (\App\Helpers\Auth::can('inventory.view_cost')): ?>
                            <th class="text-end">Cost</th>
                            <?php endif; ?>
                            <th class="text-end">Total</th>
                        </tr></thead>
                        <tbody>
                        <?php foreach ($sale['items'] as $i => $item): ?>
                        <tr>
                            <td><?= $i+1 ?></td>
                            <td>
                                <div class="fw-semibold"><?= htmlspecialchars($item['product_name']) ?></div>
                                <div class="text-muted" style="font-size:.72rem"><?= htmlspecialchars($item['sku']) ?></div>
                            </td>
                            <td><code style="font-size:.72rem"><?= htmlspecialchars($item['batch_code']) ?></code></td>
                            <td class="text-center"><?= $item['quantity'] ?></td>
                            <td class="text-end"><?= fmt($item['unit_price']) ?></td>
                            <?php if (\App\Helpers\Auth::can('inventory.view_cost')): ?>
                            <td class="text-end text-muted"><?= fmt($item['unit_cost']) ?></td>
                            <?php endif; ?>
                            <td class="text-end fw-semibold"><?= fmt($item['line_total']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr><td colspan="<?= \App\Helpers\Auth::can('inventory.view_cost') ? 6 : 5 ?>" class="text-end text-muted">Subtotal</td>
                                <td class="text-end fw-semibold"><?= fmt($sale['subtotal']) ?></td></tr>
                            <?php if ((float)$sale['discount_amount'] > 0): ?>
                            <tr><td colspan="<?= \App\Helpers\Auth::can('inventory.view_cost') ? 6 : 5 ?>" class="text-end text-muted">
                                Discount (<?= $sale['discount_type']==='percent' ? $sale['discount_value'].'%' : '-$'.$sale['discount_value'] ?>)
                            </td>
                                <td class="text-end text-danger">−<?= fmt($sale['discount_amount']) ?></td></tr>
                            <?php endif; ?>
                            <?php if ((float)$sale['tax_amount'] > 0): ?>
                            <tr><td colspan="<?= \App\Helpers\Auth::can('inventory.view_cost') ? 6 : 5 ?>" class="text-end text-muted">Tax (<?= $sale['tax_rate'] ?>%)</td>
                                <td class="text-end"><?= fmt($sale['tax_amount']) ?></td></tr>
                            <?php endif; ?>
                            <tr class="table-primary fw-bold">
                                <td colspan="<?= \App\Helpers\Auth::can('inventory.view_cost') ? 6 : 5 ?>" class="text-end">TOTAL</td>
                                <td class="text-end fs-5"><?= fmt($sale['total_amount']) ?></td>
                            </tr>
                            <?php if (\App\Helpers\Auth::can('sales.view_profit')): ?>
                            <tr><td colspan="<?= \App\Helpers\Auth::can('inventory.view_cost') ? 6 : 5 ?>" class="text-end text-muted">Gross Profit</td>
                                <td class="text-end <?= (float)$sale['gross_profit']>=0?'text-success fw-semibold':'text-danger fw-semibold' ?>"><?= fmt($sale['gross_profit']) ?></td></tr>
                            <?php endif; ?>
                        </tfoot>
                    </table>
                </div>

                <?php if ($sale['notes']): ?>
                <div class="alert alert-light py-2 small"><i class="bi bi-chat-text me-1"></i><?= htmlspecialchars($sale['notes']) ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Sidebar info -->
    <div class="col-12 col-lg-4">
        <div class="card mb-3">
            <div class="card-header"><i class="bi bi-person me-2 text-primary"></i>Customer</div>
            <div class="card-body small">
                <?php $custName = $sale['customer_full_name'] ?? $sale['customer_name'] ?? 'Walk-in Customer'; ?>
                <div class="fw-semibold"><?= htmlspecialchars($custName) ?></div>
                <?php if ($sale['customer_phone']): ?><div class="text-muted"><?= htmlspecialchars($sale['customer_phone']) ?></div><?php endif; ?>
                <?php if ($sale['customer_id']): ?>
                <a href="<?= APP_URL ?>/customers/<?= $sale['customer_id'] ?>" class="btn btn-xs btn-outline-primary mt-2">View Profile</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><i class="bi bi-credit-card me-2 text-primary"></i>Payment</div>
            <div class="card-body small">
                <table class="table table-sm mb-0">
                    <tr><td class="text-muted">Method</td><td class="fw-semibold"><?= ucfirst(str_replace('_',' ',$sale['payment_method'])) ?></td></tr>
                    <tr><td class="text-muted">Total</td><td class="fw-bold"><?= fmt($sale['total_amount']) ?></td></tr>
                    <tr><td class="text-muted">Paid</td><td class="text-success"><?= fmt($sale['amount_paid']) ?></td></tr>
                    <tr><td class="text-muted">Balance</td>
                        <td class="<?= (float)$sale['amount_due']>0?'text-danger fw-semibold':'' ?>"><?= fmt($sale['amount_due']) ?></td></tr>
                    <tr><td class="text-muted">Status</td>
                        <td><span class="badge text-bg-<?= $payBadge[$sale['payment_status']] ?>"><?= ucfirst($sale['payment_status']) ?></span></td></tr>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><i class="bi bi-info-circle me-2 text-primary"></i>Transaction Info</div>
            <div class="card-body small">
                <table class="table table-sm mb-0">
                    <tr><td class="text-muted">Invoice</td><td><code><?= htmlspecialchars($sale['invoice_number']) ?></code></td></tr>
                    <tr><td class="text-muted">Date</td><td><?= date('d/m/Y', strtotime($sale['sale_date'])) ?></td></tr>
                    <tr><td class="text-muted">Served By</td><td><?= htmlspecialchars($sale['served_by_name'] ?? '—') ?></td></tr>
                    <tr><td class="text-muted">Created</td><td><?= date('d/m/Y H:i', strtotime($sale['created_at'])) ?></td></tr>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="voidModal" tabindex="-1">
    <div class="modal-dialog modal-sm"><div class="modal-content">
        <div class="modal-header border-0"><h6 class="modal-title text-danger"><i class="bi bi-x-circle me-2"></i>Void Sale</h6></div>
        <div class="modal-body">
            <p class="small text-muted">This will reverse all stock deductions. This cannot be undone.</p>
            <input type="text" id="voidReason" class="form-control form-control-sm" placeholder="Reason *" required>
        </div>
        <div class="modal-footer border-0 pt-0">
            <button class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button class="btn btn-sm btn-danger" onclick="confirmVoid()">Void</button>
        </div>
    </div></div>
</div>

<style>.btn-xs{padding:.2rem .45rem;font-size:.75rem;}</style>
<script>
function voidThis() { new bootstrap.Modal(document.getElementById('voidModal')).show(); }
async function confirmVoid() {
    const reason = document.getElementById('voidReason').value.trim();
    if (!reason) { App.toast('Reason required.','warning'); return; }
    try {
        await App.fetch(`${APP_URL}/sales/<?= $sale['id'] ?>/void`, { method:'POST', body:{reason, _csrf_token: CSRF_TOKEN} });
        App.toast('Sale voided.','success');
        setTimeout(()=>location.reload(), 800);
    } catch(e) { App.toast(e.message,'danger'); }
}
</script>

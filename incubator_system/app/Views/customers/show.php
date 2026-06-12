<?php /* customers/show.php */
function fmt($v){return '$'.number_format((float)$v,2);}
?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center">
        <a href="<?= APP_URL ?>/customers" class="btn btn-sm btn-outline-secondary me-3"><i class="bi bi-arrow-left"></i></a>
        <div>
            <h1 class="page-title"><?= htmlspecialchars($customer['full_name']) ?></h1>
            <p class="page-subtitle mb-0"><?= htmlspecialchars($customer['customer_code']) ?></p>
        </div>
    </div>
    <?php if (\App\Helpers\Auth::can('customers.edit')): ?>
    <a href="<?= APP_URL ?>/customers/<?= $customer['id'] ?>/edit" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-pencil me-1"></i>Edit
    </a>
    <?php endif; ?>
</div>

<div class="row g-3 mb-4">
    <?php $statItems=[['Orders',$stats['total_orders'],'receipt','primary'],['Lifetime Value',fmt($stats['lifetime_value']),'cash-stack','success'],['Largest Order',fmt($stats['largest_order']),'graph-up','warning'],['Avg Order',fmt($stats['avg_order']),'calculator','info']];
    foreach ($statItems as [$l,$v,$i,$c]): ?>
    <div class="col-6 col-md-3">
        <div class="kpi-card <?= $c ?>">
            <div class="kpi-icon <?= $c ?> mb-2"><i class="bi bi-<?= $i ?>"></i></div>
            <div class="kpi-value fs-4"><?= $v ?></div>
            <div class="kpi-label"><?= $l ?></div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="row g-3">
    <div class="col-12 col-md-4">
        <div class="card">
            <div class="card-header"><i class="bi bi-person me-2 text-primary"></i>Contact Info</div>
            <div class="card-body small">
                <table class="table table-sm mb-0">
                    <tr><td class="text-muted">Code</td><td><code><?= htmlspecialchars($customer['customer_code'] ?? '—') ?></code></td></tr>
                    <tr><td class="text-muted">Phone</td><td><?= htmlspecialchars($customer['phone'] ?? '—') ?></td></tr>
                    <tr><td class="text-muted">Email</td><td><?= htmlspecialchars($customer['email'] ?? '—') ?></td></tr>
                    <tr><td class="text-muted">Address</td><td><?= htmlspecialchars($customer['address'] ?? '—') ?></td></tr>
                    <tr><td class="text-muted">City</td><td><?= htmlspecialchars($customer['city'] ?? '—') ?></td></tr>
                    <tr><td class="text-muted">Member Since</td><td><?= $customer['created_at'] ? date('d/m/Y', strtotime($customer['created_at'])) : '—' ?></td></tr>
                    <tr><td class="text-muted">Status</td><td>
                        <span class="badge <?= $customer['is_active']?'text-bg-success':'text-bg-secondary' ?>">
                            <?= $customer['is_active']?'Active':'Inactive' ?>
                        </span>
                    </td></tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-8">
        <div class="card">
            <div class="card-header"><i class="bi bi-receipt me-2 text-primary"></i>Purchase History</div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 small">
                    <thead><tr><th>Invoice</th><th>Date</th><th>Items</th><th class="text-end">Total</th><th>Payment</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php if (empty($purchases)): ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">No purchases yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($purchases as $p): ?>
                        <tr onclick="window.location='<?= APP_URL ?>/sales/<?= $p['id'] ?>'" style="cursor:pointer">
                            <td><code><?= htmlspecialchars($p['invoice_number'] ?? '') ?></code></td>
                            <td><?= $p['sale_date'] ? date('d/m/Y', strtotime($p['sale_date'])) : '—' ?></td>
                            <td><?= $p['item_count'] ?></td>
                            <td class="text-end fw-semibold"><?= fmt($p['total_amount']) ?></td>
                            <td><?= ucfirst($p['payment_method'] ?? '') ?></td>
                            <td><span class="badge status-<?= $p['status'] ?? '' ?>"><?= ucfirst($p['status'] ?? '') ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
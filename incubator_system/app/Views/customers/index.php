<?php /* customers/index.php */
function fmt($v){return '$'.number_format((float)$v,2);}
?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <div><h1 class="page-title">Customers</h1><p class="page-subtitle mb-0">Manage customer records and purchase history</p></div>
    <?php if (\App\Helpers\Auth::can('customers.create')): ?>
    <a href="<?= APP_URL ?>/customers/create" class="btn btn-primary btn-sm"><i class="bi bi-person-plus me-1"></i>Add Customer</a>
    <?php endif; ?>
</div>

<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="d-flex gap-2">
            <input type="text" name="q" class="form-control form-control-sm" placeholder="Search name, phone, email, code…" value="<?= htmlspecialchars($search) ?>">
            <button class="btn btn-sm btn-primary"><i class="bi bi-search me-1"></i>Search</button>
            <?php if ($search): ?><a href="<?= APP_URL ?>/customers" class="btn btn-sm btn-outline-secondary">Clear</a><?php endif; ?>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 small" id="custTable">
            <thead><tr>
                <th data-sort="customer_code">Code</th>
                <th data-sort="full_name">Name</th>
                <th>Phone</th>
                <th>City</th>
                <th class="text-end" data-sort="total_orders">Orders</th>
                <th class="text-end" data-sort="lifetime_value">Lifetime Value</th>
                <th>Status</th>
                <th>Actions</th>
            </tr></thead>
            <tbody>
            <?php if (empty($customers)): ?>
                <tr><td colspan="8" class="text-center py-5 text-muted">
                    <i class="bi bi-people d-block fs-2 mb-2 opacity-25"></i>No customers found.
                </td></tr>
            <?php else: ?>
                <?php foreach ($customers as $c): ?>
                <tr>
                    <td><code class="small"><?= htmlspecialchars($c['customer_code']) ?></code></td>
                    <td><a href="<?= APP_URL ?>/customers/<?= $c['id'] ?>" class="text-decoration-none fw-semibold"><?= htmlspecialchars($c['full_name']) ?></a></td>
                    <td><?= htmlspecialchars($c['phone'] ?: '—') ?></td>
                    <td><?= htmlspecialchars($c['city'] ?: '—') ?></td>
                    <td class="text-end"><?= number_format($c['total_orders']) ?></td>
                    <td class="text-end fw-semibold"><?= fmt($c['lifetime_value']) ?></td>
                    <td><span class="badge <?= $c['is_active']?'text-bg-success':'text-bg-secondary' ?>"><?= $c['is_active']?'Active':'Inactive' ?></span></td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="<?= APP_URL ?>/customers/<?= $c['id'] ?>" class="btn btn-xs btn-outline-secondary"><i class="bi bi-eye"></i></a>
                            <?php if (\App\Helpers\Auth::can('customers.edit')): ?>
                            <a href="<?= APP_URL ?>/customers/<?= $c['id'] ?>/edit" class="btn btn-xs btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if ($paginate['pages']>1): ?>
    <div class="card-footer d-flex align-items-center justify-content-between small text-muted">
        <span>Showing <?= count($customers) ?> of <?= number_format($paginate['total']) ?></span>
        <nav><ul class="pagination pagination-sm mb-0">
            <?php for($i=1;$i<=$paginate['pages'];$i++): ?>
            <li class="page-item <?= $paginate['page']==$i?'active':'' ?>"><a class="page-link" href="?<?= http_build_query(array_merge($_GET,['page'=>$i])) ?>"><?= $i ?></a></li>
            <?php endfor; ?>
        </ul></nav>
    </div>
    <?php endif; ?>
</div>
<style>.btn-xs{padding:.2rem .45rem;font-size:.75rem;}</style>
<script>document.addEventListener('DOMContentLoaded',()=>DataTable.init('custTable'));</script>

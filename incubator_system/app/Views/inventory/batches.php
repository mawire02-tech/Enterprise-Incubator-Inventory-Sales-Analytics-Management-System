<?php
function fmt($v) { return '$' . number_format((float)$v, 2); }
function fmtN($v) { return number_format((int)$v); }
$statusColors = ['active'=>'primary','depleted'=>'warning','archived'=>'secondary'];
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="page-title">Inventory Batches</h1>
        <p class="page-subtitle mb-0">Track stock batches with FIFO cost tracking</p>
    </div>
    <?php if (\App\Helpers\Auth::can('inventory.receive')): ?>
    <a href="<?= APP_URL ?>/inventory/batches/receive" class="btn btn-primary btn-sm">
        <i class="bi bi-box-arrow-in-down me-1"></i>Receive Stock
    </a>
    <?php endif; ?>
</div>

<!-- Filters -->
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-sm-4 col-md-3">
                <label class="form-label mb-1" style="font-size:.75rem">Product</label>
                <select name="product_id" class="form-select form-select-sm">
                    <option value="">All Products</option>
                    <?php foreach ($products as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= (int)($product_id??0) === $p['id'] ? 'selected':'' ?>>
                        <?= htmlspecialchars($p['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-sm-4 col-md-2">
                <label class="form-label mb-1" style="font-size:.75rem">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="all" <?= $status==='all'?'selected':'' ?>>All</option>
                    <option value="active" <?= $status==='active'?'selected':'' ?>>Active</option>
                    <option value="depleted" <?= $status==='depleted'?'selected':'' ?>>Depleted</option>
                    <option value="archived" <?= $status==='archived'?'selected':'' ?>>Archived</option>
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-primary"><i class="bi bi-funnel me-1"></i>Filter</button>
                <a href="<?= APP_URL ?>/inventory/batches" class="btn btn-sm btn-outline-secondary ms-1">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 small" id="batchTable">
            <thead>
                <tr>
                    <th data-sort="batch_code">Batch Code</th>
                    <th data-sort="product_name">Product</th>
                    <th class="text-end" data-sort="quantity_received">Received</th>
                    <th class="text-end" data-sort="quantity_current">Current</th>
                    <th class="text-end">Sold</th>
                    <th class="text-end">Damaged</th>
                    <?php if (\App\Helpers\Auth::can('inventory.view_cost')): ?>
                    <th class="text-end">Cost</th>
                    <?php endif; ?>
                    <th class="text-end">Sell Price</th>
                    <th data-sort="acquisition_date">Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($batches)): ?>
                <tr><td colspan="11" class="text-center py-5 text-muted">
                    <i class="bi bi-inbox d-block fs-2 mb-2 opacity-25"></i>
                    No batches found.
                </td></tr>
            <?php else: ?>
                <?php foreach ($batches as $b):
                    $pct  = $b['quantity_received'] > 0 ? round(($b['quantity_current'] / $b['quantity_received']) * 100) : 0;
                    $barC = $pct > 40 ? '#16a34a' : ($pct > 10 ? '#d97706' : '#dc2626');
                ?>
                <tr>
                    <td>
                        <a href="<?= APP_URL ?>/inventory/batches/<?= $b['id'] ?>" class="text-decoration-none fw-semibold">
                            <code><?= htmlspecialchars($b['batch_code']) ?></code>
                        </a>
                    </td>
                    <td>
                        <div class="fw-medium"><?= htmlspecialchars($b['product_name']) ?></div>
                        <div class="text-muted" style="font-size:.7rem"><?= htmlspecialchars($b['sku']) ?></div>
                    </td>
                    <td class="text-end"><?= fmtN($b['quantity_received']) ?></td>
                    <td class="text-end">
                        <div><?= fmtN($b['quantity_current']) ?></div>
                        <div class="stock-bar mt-1" style="width:60px;margin-left:auto">
                            <div class="stock-bar-fill" style="width:<?= $pct ?>%;background:<?= $barC ?>"></div>
                        </div>
                    </td>
                    <td class="text-end"><?= fmtN($b['quantity_sold']) ?></td>
                    <td class="text-end"><?= fmtN($b['quantity_damaged']) > 0 ? '<span class="text-danger">' . fmtN($b['quantity_damaged']) . '</span>' : '0' ?></td>
                    <?php if (\App\Helpers\Auth::can('inventory.view_cost')): ?>
                    <td class="text-end"><?= fmt($b['cost_price']) ?></td>
                    <?php endif; ?>
                    <td class="text-end fw-semibold"><?= fmt($b['selling_price']) ?></td>
                    <td><?= date('d/m/Y', strtotime($b['acquisition_date'])) ?></td>
                    <td>
                        <span class="badge status-<?= $b['status'] ?>">
                            <?= ucfirst($b['status']) ?>
                        </span>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="<?= APP_URL ?>/inventory/batches/<?= $b['id'] ?>" class="btn btn-xs btn-outline-secondary" title="View Detail">
                                <i class="bi bi-eye"></i>
                            </a>
                            <?php if ($b['status'] === 'active' && \App\Helpers\Auth::can('inventory.adjust')): ?>
                            <a href="<?= APP_URL ?>/inventory/batches/<?= $b['id'] ?>/adjust" class="btn btn-xs btn-outline-warning" title="Adjust Stock">
                                <i class="bi bi-sliders"></i>
                            </a>
                            <?php endif; ?>
                            <?php if ($b['status'] === 'depleted' && \App\Helpers\Auth::can('inventory.edit')): ?>
                            <form method="POST" action="<?= APP_URL ?>/inventory/batches/<?= $b['id'] ?>/archive" class="d-inline">
                                <?= \App\Helpers\CSRF::field() ?>
                                <button type="submit" class="btn btn-xs btn-outline-info" title="Archive Batch"
                                        onclick="return confirm('Archive this batch and finalise P&L?')">
                                    <i class="bi bi-archive"></i>
                                </button>
                            </form>
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
        <span>Showing <?= count($batches) ?> of <?= number_format($paginate['total']) ?> batches</span>
        <nav><ul class="pagination pagination-sm mb-0">
            <?php for ($i = 1; $i <= $paginate['pages']; $i++): ?>
            <li class="page-item <?= $paginate['page'] == $i ? 'active' : '' ?>">
                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
            </li>
            <?php endfor; ?>
        </ul></nav>
    </div>
    <?php endif; ?>
</div>

<style>.btn-xs{padding:.2rem .45rem;font-size:.75rem;}</style>
<script>document.addEventListener('DOMContentLoaded',()=>DataTable.init('batchTable'));</script>

<?php
function fmt($v) { return '$' . number_format((float)$v, 2); }
function fmtN($v) { return number_format((float)$v); }
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="page-title">Products</h1>
        <p class="page-subtitle mb-0">Manage your incubator product catalog</p>
    </div>
    <?php if (\App\Helpers\Auth::can('products.create')): ?>
    <a href="<?= APP_URL ?>/inventory/products/create" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i>Add Product
    </a>
    <?php endif; ?>
</div>

<!-- Search -->
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="d-flex gap-2">
            <input type="text" name="q" class="form-control form-control-sm" placeholder="Search by name, SKU, brand…" value="<?= htmlspecialchars($search) ?>">
            <button class="btn btn-sm btn-primary"><i class="bi bi-search me-1"></i>Search</button>
            <?php if ($search): ?><a href="<?= APP_URL ?>/inventory/products" class="btn btn-sm btn-outline-secondary">Clear</a><?php endif; ?>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0" id="productsTable">
            <thead>
                <tr>
                    <th data-sort="sku">SKU</th>
                    <th data-sort="name">Product Name</th>
                    <th>Category</th>
                    <th>Capacity</th>
                    <th data-sort="stock_qty" class="text-end">In Stock</th>
                    <th class="text-end">Stock Value</th>
                    <th>Threshold</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($products)): ?>
                <tr><td colspan="9" class="text-center py-5 text-muted">
                    <i class="bi bi-box2 d-block fs-2 mb-2 opacity-25"></i>
                    No products found<?= $search ? ' matching "' . htmlspecialchars($search) . '"' : '' ?>.
                </td></tr>
            <?php else: ?>
                <?php foreach ($products as $p): ?>
                <?php $qty = (int)$p['stock_qty']; $low = $qty > 0 && $qty <= $p['low_stock_threshold']; ?>
                <tr>
                    <td><code class="small"><?= htmlspecialchars($p['sku']) ?></code></td>
                    <td>
                        <div class="fw-semibold small"><?= htmlspecialchars($p['name']) ?></div>
                        <?php if ($p['brand']): ?>
                            <div class="text-muted" style="font-size:.72rem"><?= htmlspecialchars($p['brand']) ?> <?= htmlspecialchars($p['model']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td class="small"><?= htmlspecialchars($p['category'] ?? '—') ?></td>
                    <td class="small"><?= $p['capacity'] ? fmtN($p['capacity']) . ' eggs' : '—' ?></td>
                    <td class="text-end">
                        <?php if ($qty === 0): ?>
                            <span class="badge status-out">Out of Stock</span>
                        <?php elseif ($low): ?>
                            <span class="badge status-low"><?= fmtN($qty) ?></span>
                        <?php else: ?>
                            <span class="fw-semibold small"><?= fmtN($qty) ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end small fw-semibold"><?= fmt($p['stock_value']) ?></td>
                    <td class="small text-muted"><?= fmtN($p['low_stock_threshold']) ?></td>
                    <td>
                        <span class="badge <?= $p['is_active'] ? 'text-bg-success' : 'text-bg-secondary' ?>">
                            <?= $p['is_active'] ? 'Active' : 'Inactive' ?>
                        </span>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <?php if (\App\Helpers\Auth::can('products.edit')): ?>
                            <a href="<?= APP_URL ?>/inventory/products/<?= $p['id'] ?>/edit" class="btn btn-xs btn-outline-secondary" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <?php endif; ?>
                            <?php if (\App\Helpers\Auth::can('inventory.view')): ?>
                            <a href="<?= APP_URL ?>/inventory/batches?product_id=<?= $p['id'] ?>" class="btn btn-xs btn-outline-primary" title="View Batches">
                                <i class="bi bi-layers"></i>
                            </a>
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
        <span>Showing <?= count($products) ?> of <?= number_format($paginate['total']) ?> products</span>
        <nav>
            <ul class="pagination pagination-sm mb-0">
                <?php for ($i = 1; $i <= $paginate['pages']; $i++): ?>
                <li class="page-item <?= $paginate['page'] == $i ? 'active' : '' ?>">
                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>

<style>.btn-xs { padding:.2rem .45rem; font-size:.75rem; }</style>
<script>document.addEventListener('DOMContentLoaded', () => DataTable.init('productsTable'));</script>

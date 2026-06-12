<div class="d-flex align-items-center mb-4">
    <a href="<?= APP_URL ?>/inventory/products" class="btn btn-sm btn-outline-secondary me-3">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h1 class="page-title"><?= $product ? 'Edit Product' : 'Add Product' ?></h1>
        <p class="page-subtitle mb-0"><?= $product ? 'Update product catalog entry' : 'Register a new incubator product' ?></p>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-8">
        <div class="card">
            <div class="card-header">Product Details</div>
            <div class="card-body">
                <form method="POST" action="<?= APP_URL ?>/inventory/products/<?= $product ? $product['id'].'/edit' : 'create' ?>">
                    <?= \App\Helpers\CSRF::field() ?>

                    <div class="row g-3">
                        <div class="col-sm-4">
                            <label class="form-label">SKU <span class="text-danger">*</span></label>
                            <input type="text" name="sku" class="form-control"
                                   value="<?= htmlspecialchars($product['sku'] ?? '') ?>"
                                   placeholder="e.g. INC-48"
                                   <?= $product ? 'readonly' : 'required' ?>>
                            <div class="form-text">Unique identifier. Cannot be changed after creation.</div>
                        </div>
                        <div class="col-sm-8">
                            <label class="form-label">Product Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control"
                                   value="<?= htmlspecialchars($product['name'] ?? '') ?>"
                                   placeholder="e.g. 48-Egg Automatic Incubator" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3"
                                      placeholder="Product description..."><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
                        </div>

                        <div class="col-sm-4">
                            <label class="form-label">Category</label>
                            <select name="category" class="form-select">
                                <option value="">-- Select --</option>
                                <?php foreach (['Automatic','Semi-Auto','Commercial','Industrial','Accessory'] as $cat): ?>
                                <option value="<?= $cat ?>" <?= ($product['category'] ?? '') === $cat ? 'selected' : '' ?>><?= $cat ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label">Egg Capacity</label>
                            <input type="number" name="capacity" class="form-control" min="0"
                                   value="<?= htmlspecialchars($product['capacity'] ?? '') ?>"
                                   placeholder="e.g. 48">
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label">Unit</label>
                            <select name="unit" class="form-select">
                                <option value="unit" <?= ($product['unit'] ?? 'unit') === 'unit' ? 'selected' : '' ?>>Unit</option>
                                <option value="piece" <?= ($product['unit'] ?? '') === 'piece' ? 'selected' : '' ?>>Piece</option>
                                <option value="set" <?= ($product['unit'] ?? '') === 'set' ? 'selected' : '' ?>>Set</option>
                            </select>
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label">Brand</label>
                            <input type="text" name="brand" class="form-control"
                                   value="<?= htmlspecialchars($product['brand'] ?? '') ?>"
                                   placeholder="e.g. HatchPro">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Model</label>
                            <input type="text" name="model" class="form-control"
                                   value="<?= htmlspecialchars($product['model'] ?? '') ?>"
                                   placeholder="e.g. HP-48A">
                        </div>

                        <div class="col-sm-4">
                            <label class="form-label">Low Stock Threshold</label>
                            <input type="number" name="low_stock_threshold" class="form-control" min="0"
                                   value="<?= htmlspecialchars($product['low_stock_threshold'] ?? 5) ?>">
                            <div class="form-text">Alert when stock falls below this.</div>
                        </div>

                        <?php if ($product): ?>
                        <div class="col-sm-4">
                            <label class="form-label">Status</label>
                            <select name="is_active" class="form-select">
                                <option value="1" <?= ($product['is_active'] ?? 1) ? 'selected' : '' ?>>Active</option>
                                <option value="0" <?= !($product['is_active'] ?? 1) ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i><?= $product ? 'Update Product' : 'Create Product' ?>
                        </button>
                        <a href="<?= APP_URL ?>/inventory/products" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="card border-primary-subtle">
            <div class="card-header text-primary"><i class="bi bi-lightbulb me-2"></i>Tips</div>
            <div class="card-body small text-muted">
                <p><strong>SKU</strong> — Use a consistent format like <code>INC-48</code> for easy identification.</p>
                <p><strong>Category</strong> — Helps group products in reports and filters.</p>
                <p><strong>Low Stock Threshold</strong> — You'll get an alert when total available stock of this product drops to or below this number.</p>
                <p class="mb-0"><strong>Capacity</strong> — Total egg capacity for incubators; leave blank for accessories.</p>
            </div>
        </div>
    </div>
</div>

<?php /* customers/form.php */ ?>
<div class="d-flex align-items-center mb-4">
    <a href="<?= APP_URL ?>/customers" class="btn btn-sm btn-outline-secondary me-3"><i class="bi bi-arrow-left"></i></a>
    <div><h1 class="page-title"><?= $customer ? 'Edit Customer' : 'Add Customer' ?></h1></div>
</div>
<div class="row g-4">
<div class="col-12 col-lg-7">
<div class="card"><div class="card-body">
<form method="POST" action="<?= APP_URL ?>/customers/<?= $customer ? $customer['id'].'/edit' : 'create' ?>">
    <?= \App\Helpers\CSRF::field() ?>
    <div class="row g-3">
        <div class="col-12">
            <label class="form-label">Full Name <span class="text-danger">*</span></label>
            <input type="text" name="full_name" class="form-control" required value="<?= htmlspecialchars($customer['full_name']??'') ?>">
        </div>
        <div class="col-sm-6">
            <label class="form-label">Phone</label>
            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($customer['phone']??'') ?>" placeholder="+263…">
        </div>
        <div class="col-sm-6">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($customer['email']??'') ?>">
        </div>
        <div class="col-12">
            <label class="form-label">Address</label>
            <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($customer['address']??'') ?>">
        </div>
        <div class="col-sm-6">
            <label class="form-label">City</label>
            <input type="text" name="city" class="form-control" value="<?= htmlspecialchars($customer['city']??'') ?>">
        </div>
        <?php if ($customer): ?>
        <div class="col-sm-6">
            <label class="form-label">Status</label>
            <select name="is_active" class="form-select">
                <option value="1" <?= ($customer['is_active']??1)?'selected':'' ?>>Active</option>
                <option value="0" <?= !($customer['is_active']??1)?'selected':'' ?>>Inactive</option>
            </select>
        </div>
        <?php endif; ?>
        <div class="col-12">
            <label class="form-label">Notes</label>
            <textarea name="notes" class="form-control" rows="2"><?= htmlspecialchars($customer['notes']??'') ?></textarea>
        </div>
    </div>
    <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i><?= $customer?'Update':'Save Customer' ?></button>
        <a href="<?= APP_URL ?>/customers" class="btn btn-outline-secondary">Cancel</a>
    </div>
</form>
</div></div>
</div>
</div>

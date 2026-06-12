<?php /* auth/change_password.php */ ?>
<div class="d-flex align-items-center mb-4">
    <a href="<?= APP_URL ?>/profile" class="btn btn-sm btn-outline-secondary me-3"><i class="bi bi-arrow-left"></i></a>
    <div><h1 class="page-title">Change Password</h1></div>
</div>
<div class="row g-4">
    <div class="col-12 col-sm-8 col-md-6 col-lg-5">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="<?= APP_URL ?>/profile/change-password">
                    <?= \App\Helpers\CSRF::field() ?>
                    <div class="mb-3">
                        <label class="form-label">Current Password <span class="text-danger">*</span></label>
                        <input type="password" name="current_password" class="form-control" required autocomplete="current-password">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Password <span class="text-danger">*</span></label>
                        <input type="password" name="new_password" id="newPwd" class="form-control" required minlength="8" autocomplete="new-password">
                        <div class="form-text">Minimum 8 characters.</div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                        <input type="password" name="new_password_confirmation" id="confirmPwd" class="form-control" required minlength="8" autocomplete="new-password">
                        <div id="pwdMatch" class="form-text"></div>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-shield-check me-1"></i>Update Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
document.getElementById('confirmPwd').addEventListener('input', function() {
    const match = this.value === document.getElementById('newPwd').value;
    this.classList.toggle('is-valid', match && this.value.length >= 8);
    this.classList.toggle('is-invalid', !match && this.value.length > 0);
    document.getElementById('pwdMatch').textContent = match ? '✓ Passwords match' : 'Passwords do not match';
    document.getElementById('pwdMatch').className = match ? 'form-text text-success' : 'form-text text-danger';
});
</script>

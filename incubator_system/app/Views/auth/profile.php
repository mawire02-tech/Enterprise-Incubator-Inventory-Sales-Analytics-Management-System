<?php /* auth/profile.php */ ?>
<div class="mb-4">
    <h1 class="page-title">My Profile</h1>
    <p class="page-subtitle mb-0">Update your personal information</p>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-6">
        <div class="card">
            <div class="card-header"><i class="bi bi-person me-2 text-primary"></i>Personal Details</div>
            <div class="card-body">
                <form method="POST" action="<?= APP_URL ?>/profile">
                    <?= \App\Helpers\CSRF::field() ?>
                    <div class="text-center mb-4">
                        <div class="user-avatar mx-auto" style="width:64px;height:64px;font-size:1.6rem">
                            <?= strtoupper(substr($currentUser['full_name'],0,1)) ?>
                        </div>
                        <div class="fw-bold mt-2"><?= htmlspecialchars($currentUser['username']) ?></div>
                        <span class="badge text-bg-primary"><?= htmlspecialchars($currentUser['role_display']) ?></span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="full_name" class="form-control" required value="<?= htmlspecialchars($user['full_name']) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($user['email']) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone']??'') ?>" placeholder="+263…">
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i>Save Changes
                    </button>
                    <a href="<?= APP_URL ?>/profile/change-password" class="btn btn-outline-warning ms-2">
                        <i class="bi bi-key me-1"></i>Change Password
                    </a>
                </form>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-6">
        <div class="card">
            <div class="card-header"><i class="bi bi-shield me-2 text-primary"></i>Account Security</div>
            <div class="card-body small">
                <table class="table table-sm mb-0">
                    <tr><td class="text-muted">Username</td><td><code><?= htmlspecialchars($user['username']) ?></code></td></tr>
                    <tr><td class="text-muted">Role</td><td><span class="badge text-bg-primary"><?= htmlspecialchars($user['role_display']) ?></span></td></tr>
                    <tr><td class="text-muted">Last Login</td><td><?= $user['last_login_at'] ? date('d/m/Y H:i',strtotime($user['last_login_at'])) : 'N/A' ?></td></tr>
                    <tr><td class="text-muted">Last Login IP</td><td><?= htmlspecialchars($user['last_login_ip']??'—') ?></td></tr>
                    <tr><td class="text-muted">Password Changed</td><td><?= $user['password_changed_at'] ? date('d/m/Y',strtotime($user['password_changed_at'])) : '—' ?></td></tr>
                    <tr><td class="text-muted">Member Since</td><td><?= date('d/m/Y',strtotime($user['created_at'])) ?></td></tr>
                </table>
            </div>
        </div>
    </div>
</div>

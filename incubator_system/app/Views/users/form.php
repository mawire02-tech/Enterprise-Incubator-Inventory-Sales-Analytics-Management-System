<?php /* users/form.php */ ?>

<?php
$isEdit      = (bool)$user;
$pageHeading = $isEdit ? 'Edit User' : 'Create User';
$subheading  = $isEdit ? htmlspecialchars($user['username']) : 'New system account';
$formAction  = APP_URL . '/users/' . ($isEdit ? $user['id'].'/edit' : 'create');
?>

<!-- ── Page header ─────────────────────────────────────────── -->
<div class="d-flex align-items-center gap-3 mb-4">
    <a href="<?= APP_URL ?>/users" class="btn btn-sm btn-outline-secondary d-flex align-items-center justify-content-center" style="width:34px;height:34px;padding:0;flex-shrink:0">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h1 class="page-title"><?= $pageHeading ?></h1>
        <p class="page-subtitle mb-0"><?= $subheading ?></p>
    </div>
</div>

<div class="row g-4">

    <!-- ── Left column ─────────────────────────────────────── -->
    <div class="col-12 col-lg-8">

        <!-- Account Details card -->
        <div class="card mb-4">
            <div class="card-header d-flex align-items-center gap-2">
                <span class="kpi-icon primary" style="width:30px;height:30px;font-size:.9rem;border-radius:var(--radius-sm)">
                    <i class="bi bi-person-fill"></i>
                </span>
                <span>Account Details</span>
            </div>

            <div class="card-body p-4">
                <form method="POST" action="<?= $formAction ?>" id="userForm" autocomplete="off">
                    <?= \App\Helpers\CSRF::field() ?>

                    <!-- Section: Identity -->
                    <p class="text-uppercase fw-semibold mb-2" style="font-size:.7rem;letter-spacing:.8px;color:var(--text-muted)">Identity</p>
                    <div class="row g-3 mb-4">
                        <div class="col-sm-6">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="full_name" class="form-control" required
                                   placeholder="e.g. Tendai Moyo"
                                   value="<?= htmlspecialchars($user['full_name'] ?? '') ?>">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Phone</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                <input type="text" name="phone" class="form-control" placeholder="+263 77 123 4567"
                                       value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                            </div>
                        </div>

                        <?php if (!$isEdit): ?>
                        <div class="col-sm-6">
                            <label class="form-label">Username <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-at"></i></span>
                                <input type="text" name="username" class="form-control" required
                                       placeholder="e.g. tmoyo" autocomplete="off">
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="col-sm-6">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" name="email" class="form-control" required
                                       placeholder="user@example.com"
                                       value="<?= htmlspecialchars($user['email'] ?? '') ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Section: Access -->
                    <p class="text-uppercase fw-semibold mb-2" style="font-size:.7rem;letter-spacing:.8px;color:var(--text-muted)">Access</p>
                    <div class="row g-3 mb-4">
                        <?php if (\App\Helpers\Auth::can('users.change_role') || !$isEdit): ?>
                        <div class="col-sm-6">
                            <label class="form-label">Role <span class="text-danger">*</span></label>
                            <select name="role_id" class="form-select" id="roleSelect">
                                <?php foreach ($roles as $r): ?>
                                <option value="<?= $r['id'] ?>"
                                    <?= ($user['role_id'] ?? 0) == $r['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($r['display_name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>

                        <?php if ($isEdit): ?>
                        <div class="col-sm-6">
                            <label class="form-label">Status</label>
                            <select name="is_active" class="form-select">
                                <option value="1" <?= ($user['is_active'] ?? 1) ? 'selected' : '' ?>>
                                    Active
                                </option>
                                <option value="0" <?= !($user['is_active'] ?? 1) ? 'selected' : '' ?>>
                                    Inactive
                                </option>
                            </select>
                        </div>
                        <?php endif; ?>
                    </div>

                    <?php if (!$isEdit): ?>
                    <!-- Section: Password (create only) -->
                    <p class="text-uppercase fw-semibold mb-2" style="font-size:.7rem;letter-spacing:.8px;color:var(--text-muted)">Password</p>
                    <div class="row g-3 mb-4">
                        <div class="col-sm-6">
                            <label class="form-label">Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" name="password" class="form-control" required
                                       minlength="8" autocomplete="new-password" id="newPassword"
                                       placeholder="Min. 8 characters">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePwd('newPassword',this)" tabindex="-1">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" name="password_confirmation" class="form-control" required
                                       minlength="8" autocomplete="new-password" id="confirmPassword"
                                       placeholder="Repeat password">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePwd('confirmPassword',this)" tabindex="-1">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="must_change_password"
                                       value="1" id="mustChange" checked>
                                <label class="form-check-label small" for="mustChange">
                                    Require password change on first login
                                </label>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Actions -->
                    <div class="d-flex align-items-center gap-2 pt-2 border-top mt-2">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check-lg me-1"></i><?= $isEdit ? 'Save Changes' : 'Create User' ?>
                        </button>
                        <a href="<?= APP_URL ?>/users" class="btn btn-outline-secondary">Cancel</a>
                    </div>

                </form>
            </div>
        </div>

        <!-- Permission Overrides (edit only, admin only) -->
        <?php if ($isEdit && \App\Helpers\Auth::can('users.change_role') && isset($allPerms)): ?>
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <span class="kpi-icon warning" style="width:30px;height:30px;font-size:.9rem;border-radius:var(--radius-sm)">
                        <i class="bi bi-shield-lock-fill"></i>
                    </span>
                    <span>Permission Overrides</span>
                </div>
                <span class="badge text-bg-info">Advanced</span>
            </div>

            <div class="card-body p-4">
                <!-- Legend -->
                <div class="d-flex flex-wrap gap-3 mb-4 p-3 rounded" style="background:var(--surface-alt);border:1px solid var(--border)">
                    <span class="d-flex align-items-center gap-1 small">
                        <span class="badge text-bg-success">Role</span> Granted via role
                    </span>
                    <span class="d-flex align-items-center gap-1 small">
                        <span class="badge text-bg-primary">Grant</span> Explicitly allowed
                    </span>
                    <span class="d-flex align-items-center gap-1 small">
                        <span class="badge text-bg-danger">Deny</span> Explicitly blocked
                    </span>
                </div>

                <?php
                $rolePermNames = array_column($rolePerms ?? [], 'name');
                $userPermMap   = array_column($userPerms ?? [], null, 'name');
                $grouped       = [];
                foreach ($allPerms as $p) {
                    $grouped[$p['module']][] = $p;
                }
                ?>

                <form method="POST" action="<?= APP_URL ?>/users/<?= $user['id'] ?>/edit" id="permForm">
                    <?= \App\Helpers\CSRF::field() ?>
                    <input type="hidden" name="full_name"  value="<?= htmlspecialchars($user['full_name']) ?>">
                    <input type="hidden" name="email"      value="<?= htmlspecialchars($user['email']) ?>">
                    <input type="hidden" name="is_active"  value="<?= $user['is_active'] ?>">

                    <?php foreach ($grouped as $module => $perms): ?>
                    <div class="mb-4">
                        <p class="text-uppercase fw-semibold mb-2" style="font-size:.7rem;letter-spacing:.8px;color:var(--text-muted)">
                            <?= ucfirst($module) ?>
                        </p>
                        <div class="row g-2">
                            <?php foreach ($perms as $perm):
                                $fromRole = in_array($perm['name'], $rolePermNames);
                                $override = $userPermMap[$perm['name']] ?? null;
                                $state    = $override
                                    ? ($override['user_granted'] ? 'grant' : 'deny')
                                    : ($fromRole ? 'role' : 'none');

                                $rowClass = match($state) {
                                    'role'  => 'border-success-subtle bg-success bg-opacity-10',
                                    'grant' => 'border-primary-subtle bg-primary bg-opacity-10',
                                    'deny'  => 'border-danger-subtle bg-danger bg-opacity-10',
                                    default => ''
                                };
                            ?>
                            <div class="col-sm-6 col-md-4">
                                <div class="d-flex align-items-center gap-2 p-2 border rounded <?= $rowClass ?>">
                                    <div class="flex-grow-1 overflow-hidden">
                                        <div class="fw-medium small text-truncate"><?= htmlspecialchars($perm['action']) ?></div>
                                        <?php if ($fromRole): ?>
                                        <span class="badge text-bg-success" style="font-size:.62em">Role</span>
                                        <?php endif; ?>
                                    </div>
                                    <select name="permissions[<?= $perm['id'] ?>]" class="form-select form-select-sm flex-shrink-0" style="width:82px">
                                        <option value=""  <?= in_array($state,['none','role']) ? 'selected' : '' ?>>Default</option>
                                        <option value="1" <?= $state === 'grant' ? 'selected' : '' ?>>Grant</option>
                                        <option value="0" <?= $state === 'deny'  ? 'selected' : '' ?>>Deny</option>
                                    </select>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <div class="pt-2 border-top">
                        <button type="submit" class="btn btn-primary btn-sm px-3">
                            <i class="bi bi-shield-check me-1"></i>Save Permissions
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- ── Right column ────────────────────────────────────── -->
    <div class="col-12 col-lg-4">

        <?php if ($isEdit): ?>

        <!-- Reset Password -->
        <?php if (\App\Helpers\Auth::can('users.edit')): ?>
        <div class="card mb-4">
            <div class="card-header d-flex align-items-center gap-2">
                <span class="kpi-icon warning" style="width:30px;height:30px;font-size:.9rem;border-radius:var(--radius-sm)">
                    <i class="bi bi-key-fill"></i>
                </span>
                <span>Reset Password</span>
            </div>
            <div class="card-body p-4">
                <label class="form-label">New Password</label>
                <div class="input-group mb-3">
                    <input type="password" class="form-control" id="resetPass" placeholder="Min. 8 characters">
                    <button class="btn btn-outline-secondary" type="button" onclick="togglePwd('resetPass',this)" tabindex="-1">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
                <button class="btn btn-warning w-100" onclick="resetPassword()">
                    <i class="bi bi-key me-1"></i>Reset Password
                </button>
            </div>
        </div>
        <?php endif; ?>

        <!-- Account Info -->
        <div class="card">
            <div class="card-header d-flex align-items-center gap-2">
                <span class="kpi-icon info" style="width:30px;height:30px;font-size:.9rem;border-radius:var(--radius-sm)">
                    <i class="bi bi-info-circle-fill"></i>
                </span>
                <span>Account Info</span>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0" style="font-size:.82rem">
                    <tbody>
                        <tr>
                            <td class="text-muted ps-4 py-2" style="width:45%">Username</td>
                            <td class="py-2 pe-4"><code><?= htmlspecialchars($user['username']) ?></code></td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-4 py-2">Created</td>
                            <td class="py-2 pe-4"><?= date('d M Y', strtotime($user['created_at'])) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-4 py-2">Last Login</td>
                            <td class="py-2 pe-4">
                                <?= $user['last_login_at']
                                    ? date('d M Y, H:i', strtotime($user['last_login_at']))
                                    : '<span class="text-muted">Never</span>' ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-4 py-2">Last IP</td>
                            <td class="py-2 pe-4"><?= htmlspecialchars($user['last_login_ip'] ?: '—') ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-4 py-2">Failed Logins</td>
                            <td class="py-2 pe-4">
                                <?php if ($user['failed_attempts'] > 0): ?>
                                    <span class="badge text-bg-warning"><?= $user['failed_attempts'] ?></span>
                                <?php else: ?>
                                    <span class="text-muted">0</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-4 py-2">Account Lock</td>
                            <td class="py-2 pe-4">
                                <?= $user['is_locked']
                                    ? '<span class="badge text-bg-danger">Locked</span>'
                                    : '<span class="badge text-bg-success">Unlocked</span>' ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <?php endif; ?>
    </div>
</div>

<!-- ── Scripts ──────────────────────────────────────────────── -->
<script>
function togglePwd(id, btn) {
    const input = document.getElementById(id);
    const icon  = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('bi-eye', 'bi-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('bi-eye-slash', 'bi-eye');
    }
}
</script>

<?php if ($isEdit && \App\Helpers\Auth::can('users.edit')): ?>
<script>
async function resetPassword() {
    const pass = document.getElementById('resetPass').value.trim();
    if (pass.length < 8) { App.toast('Password must be at least 8 characters.', 'warning'); return; }
    const ok = await App.confirm('Reset password for <?= htmlspecialchars($user['username']) ?>?');
    if (!ok) return;
    try {
        await App.fetch(`${APP_URL}/users/<?= $user['id'] ?>/reset-password`, {
            method: 'POST',
            body: { new_password: pass, _csrf_token: CSRF_TOKEN }
        });
        App.toast('Password reset. User must change on next login.', 'success');
        document.getElementById('resetPass').value = '';
    } catch(e) { App.toast(e.message, 'danger'); }
}
</script>
<?php endif; ?>
<?php /* users/index.php */ ?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <div><h1 class="page-title">User Management</h1><p class="page-subtitle mb-0">Manage system users and roles</p></div>
    <?php if (\App\Helpers\Auth::can('users.create')): ?>
    <a href="<?= APP_URL ?>/users/create" class="btn btn-primary btn-sm"><i class="bi bi-person-plus me-1"></i>Create User</a>
    <?php endif; ?>
</div>

<div class="card mb-3"><div class="card-body py-2">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-sm-4"><input type="text" name="q" class="form-control form-control-sm" placeholder="Search…" value="<?= htmlspecialchars($search) ?>"></div>
        <div class="col-sm-3">
            <select name="role_id" class="form-select form-select-sm">
                <option value="">All Roles</option>
                <?php foreach ($roles as $r): ?>
                <option value="<?= $r['id'] ?>" <?= $roleFilter==$r['id']?'selected':'' ?>><?= htmlspecialchars($r['display_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-auto"><button class="btn btn-sm btn-primary">Filter</button></div>
    </form>
</div></div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 small" id="usersTable">
            <thead><tr>
                <th>User</th><th>Username</th><th>Role</th><th>Last Login</th>
                <th>Status</th><th>Locked</th><th>Actions</th>
            </tr></thead>
            <tbody>
            <?php if (empty($users)): ?>
                <tr><td colspan="7" class="text-center text-muted py-5">No users found.</td></tr>
            <?php else: ?>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="user-avatar sm"><?= strtoupper(substr($u['full_name'],0,1)) ?></div>
                            <div>
                                <div class="fw-semibold"><?= htmlspecialchars($u['full_name']) ?></div>
                                <div class="text-muted" style="font-size:.72rem"><?= htmlspecialchars($u['email']) ?></div>
                            </div>
                        </div>
                    </td>
                    <td><code class="small"><?= htmlspecialchars($u['username']) ?></code></td>
                    <td><span class="badge text-bg-primary"><?= htmlspecialchars($u['role_display']) ?></span></td>
                    <td class="text-muted small"><?= $u['last_login_at'] ? date('d/m/Y H:i',strtotime($u['last_login_at'])) : 'Never' ?></td>
                    <td><span class="badge <?= $u['is_active']?'text-bg-success':'text-bg-secondary' ?>"><?= $u['is_active']?'Active':'Inactive' ?></span></td>
                    <td>
                        <?php if ($u['is_locked']): ?>
                        <span class="badge text-bg-danger">Locked</span>
                        <?php if (\App\Helpers\Auth::can('users.unlock')): ?>
                        <form method="POST" action="<?= APP_URL ?>/users/<?= $u['id'] ?>/unlock" class="d-inline">
                            <?= \App\Helpers\CSRF::field() ?>
                            <button class="btn btn-xs btn-outline-success ms-1">Unlock</button>
                        </form>
                        <?php endif; ?>
                        <?php else: ?>
                        <span class="text-muted small">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <?php if (\App\Helpers\Auth::can('users.edit')): ?>
                            <a href="<?= APP_URL ?>/users/<?= $u['id'] ?>/edit" class="btn btn-xs btn-outline-secondary" title="Edit"><i class="bi bi-pencil"></i></a>
                            <?php endif; ?>
                            <?php if (\App\Helpers\Auth::can('users.delete') && $u['id'] !== \App\Helpers\Auth::id()): ?>
                            <form method="POST" action="<?= APP_URL ?>/users/<?= $u['id'] ?>/delete" class="d-inline">
                                <?= \App\Helpers\CSRF::field() ?>
                                <button class="btn btn-xs btn-outline-danger" onclick="return confirm('Delete user <?= htmlspecialchars($u['username']) ?>?')" title="Delete"><i class="bi bi-trash"></i></button>
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
    <?php if ($paginate['pages']>1): ?>
    <div class="card-footer d-flex align-items-center justify-content-between small text-muted">
        <span>Showing <?= count($users) ?> of <?= number_format($paginate['total']) ?></span>
        <nav><ul class="pagination pagination-sm mb-0">
            <?php for($i=1;$i<=$paginate['pages'];$i++): ?>
            <li class="page-item <?= $paginate['page']==$i?'active':'' ?>"><a class="page-link" href="?<?= http_build_query(array_merge($_GET,['page'=>$i])) ?>"><?= $i ?></a></li>
            <?php endfor; ?>
        </ul></nav>
    </div>
    <?php endif; ?>
</div>
<style>.btn-xs{padding:.2rem .45rem;font-size:.75rem;}</style>
<script>document.addEventListener('DOMContentLoaded',()=>DataTable.init('usersTable'));</script>

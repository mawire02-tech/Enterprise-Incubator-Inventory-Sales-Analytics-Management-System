<?php /* audit/index.php */ ?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <div><h1 class="page-title">Audit Log</h1><p class="page-subtitle mb-0">Complete record of all system actions</p></div>
    <?php if (\App\Helpers\Auth::can('audit.export')): ?>
    <a href="<?= APP_URL ?>/audit/export?from=<?= $filters['date_from'] ?>&to=<?= $filters['date_to'] ?>" class="btn btn-sm btn-success">
        <i class="bi bi-download me-1"></i>Export CSV
    </a>
    <?php endif; ?>
</div>

<div class="card mb-3"><div class="card-body py-2">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-sm-3 col-md-2"><label class="form-label mb-1" style="font-size:.75rem">From</label>
            <input type="date" name="from" class="form-control form-control-sm" value="<?= $filters['date_from'] ?>"></div>
        <div class="col-sm-3 col-md-2"><label class="form-label mb-1" style="font-size:.75rem">To</label>
            <input type="date" name="to" class="form-control form-control-sm" value="<?= $filters['date_to'] ?>"></div>
        <div class="col-sm-2">
            <label class="form-label mb-1" style="font-size:.75rem">Module</label>
            <select name="module" class="form-select form-select-sm">
                <option value="">All Modules</option>
                <?php foreach ($modules as $m): ?>
                <option value="<?= htmlspecialchars($m['module']) ?>" <?= $filters['module']===$m['module']?'selected':'' ?>><?= ucfirst($m['module']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-sm-2">
            <label class="form-label mb-1" style="font-size:.75rem">Severity</label>
            <select name="severity" class="form-select form-select-sm">
                <option value="">All</option>
                <?php foreach (['low','medium','high','critical'] as $s): ?>
                <option value="<?= $s ?>" <?= $filters['severity']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-sm-4 col-md-3">
            <label class="form-label mb-1" style="font-size:.75rem">Search</label>
            <input type="text" name="q" class="form-control form-control-sm" placeholder="Action, description, user…" value="<?= htmlspecialchars($filters['search']) ?>">
        </div>
        <div class="col-auto"><button class="btn btn-sm btn-primary">Filter</button>
            <a href="<?= APP_URL ?>/audit" class="btn btn-sm btn-outline-secondary ms-1">Reset</a></div>
    </form>
</div></div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0 small">
            <thead><tr>
                <th>Date & Time</th><th>User</th><th>Module</th><th>Action</th>
                <th>Description</th><th>IP</th><th>Severity</th>
            </tr></thead>
            <tbody>
            <?php $data = $result['data'] ?? []; ?>
            <?php if (empty($data)): ?>
                <tr><td colspan="7" class="text-center text-muted py-5">
                    <i class="bi bi-shield-check d-block fs-2 mb-2 opacity-25"></i>No audit entries found.
                </td></tr>
            <?php else: ?>
                <?php foreach ($data as $log): ?>
                <tr onclick="window.location='<?= APP_URL ?>/audit/<?= $log['id'] ?>'" style="cursor:pointer">
                    <td class="text-nowrap small"><?= date('d/m/Y H:i:s',strtotime($log['created_at'])) ?></td>
                    <td>
                        <div class="fw-medium"><?= htmlspecialchars($log['full_name'] ?? $log['username'] ?? 'System') ?></div>
                        <div class="text-muted" style="font-size:.7rem"><?= htmlspecialchars($log['username'] ?? '') ?></div>
                    </td>
                    <td><span class="badge text-bg-light"><?= ucfirst($log['module']) ?></span></td>
                    <td><code class="small"><?= htmlspecialchars($log['action']) ?></code></td>
                    <td class="text-truncate" style="max-width:220px"><?= htmlspecialchars($log['description']) ?></td>
                    <td class="text-muted small"><?= htmlspecialchars($log['ip_address'] ?? '—') ?></td>
                    <td><span class="severity-<?= $log['severity'] ?> fw-semibold small"><?= ucfirst($log['severity']) ?></span></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if (($result['pages'] ?? 1) > 1): ?>
    <div class="card-footer d-flex align-items-center justify-content-between small text-muted">
        <span>Showing <?= count($data) ?> of <?= number_format($result['total']) ?> entries</span>
        <nav><ul class="pagination pagination-sm mb-0">
            <?php for ($i=1;$i<=$result['pages'];$i++): ?>
            <li class="page-item <?= $result['page']==$i?'active':'' ?>">
                <a class="page-link" href="?<?= http_build_query(array_merge($_GET,['page'=>$i])) ?>"><?= $i ?></a>
            </li>
            <?php endfor; ?>
        </ul></nav>
    </div>
    <?php endif; ?>
</div>

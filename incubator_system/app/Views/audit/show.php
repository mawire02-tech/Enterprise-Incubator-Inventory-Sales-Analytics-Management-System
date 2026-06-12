<?php /* audit/show.php */ ?>
<div class="d-flex align-items-center mb-4">
    <a href="<?= APP_URL ?>/audit" class="btn btn-sm btn-outline-secondary me-3"><i class="bi bi-arrow-left"></i></a>
    <h1 class="page-title">Audit Entry #<?= $log['id'] ?></h1>
</div>
<div class="row g-3">
    <div class="col-12 col-md-6">
        <div class="card">
            <div class="card-header"><i class="bi bi-info-circle me-2 text-primary"></i>Event Details</div>
            <div class="card-body small">
                <table class="table table-sm mb-0">
                    <tr><td class="text-muted w-35">Date & Time</td><td><?= $log['created_at'] ?></td></tr>
                    <tr><td class="text-muted">User</td><td><?= htmlspecialchars($log['full_name']??$log['username']??'System') ?></td></tr>
                    <tr><td class="text-muted">Module</td><td><span class="badge text-bg-light"><?= ucfirst($log['module']) ?></span></td></tr>
                    <tr><td class="text-muted">Action</td><td><code><?= htmlspecialchars($log['action']) ?></code></td></tr>
                    <tr><td class="text-muted">Severity</td><td><span class="severity-<?= $log['severity'] ?> fw-semibold"><?= ucfirst($log['severity']) ?></span></td></tr>
                    <tr><td class="text-muted">IP Address</td><td><?= htmlspecialchars($log['ip_address']??'—') ?></td></tr>
                    <tr><td class="text-muted">Device</td><td><?= htmlspecialchars($log['device_type']??'—') ?></td></tr>
                    <tr><td class="text-muted">Description</td><td><?= htmlspecialchars($log['description']) ?></td></tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6">
        <?php if ($log['old_values']||$log['new_values']): ?>
        <div class="card">
            <div class="card-header"><i class="bi bi-arrow-left-right me-2 text-primary"></i>Changes</div>
            <div class="card-body small">
                <div class="row g-2">
                    <?php if ($log['old_values']): ?>
                    <div class="col-6">
                        <div class="fw-semibold text-danger mb-1">Before</div>
                        <pre class="bg-light p-2 rounded small mb-0" style="font-size:.75rem;max-height:200px;overflow-y:auto"><?= htmlspecialchars(json_encode(json_decode($log['old_values'],true),JSON_PRETTY_PRINT)) ?></pre>
                    </div>
                    <?php endif; ?>
                    <?php if ($log['new_values']): ?>
                    <div class="col-6">
                        <div class="fw-semibold text-success mb-1">After</div>
                        <pre class="bg-light p-2 rounded small mb-0" style="font-size:.75rem;max-height:200px;overflow-y:auto"><?= htmlspecialchars(json_encode(json_decode($log['new_values'],true),JSON_PRETTY_PRINT)) ?></pre>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php /* settings/index.php */ ?>
<div class="mb-4">
    <h1 class="page-title">System Settings</h1>
    <p class="page-subtitle mb-0">Configure application behaviour, security and backups</p>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-8">
        <form method="POST" action="<?= APP_URL ?>/settings">
            <?= \App\Helpers\CSRF::field() ?>

            <?php
            $groupLabels = [
                'company'       => ['Company Information',   'building'],
                'finance'       => ['Finance & Tax',         'currency-dollar'],
                'sales'         => ['Sales Settings',        'receipt'],
                'inventory'     => ['Inventory Settings',    'boxes'],
                'security'      => ['Security',              'shield-lock'],
                'notifications' => ['Notifications',         'bell'],
                'ui'            => ['Display & UI',          'display'],
                'email'         => ['Email / SMTP',          'envelope'],
                'backup'        => ['Backup Settings',       'hdd'],
            ];

            foreach ($groupLabels as $grpKey => [$grpLabel, $grpIcon]):
                if (empty($grouped[$grpKey])) continue;
                // Skip backup group in main settings form (handled separately)
                if ($grpKey === 'backup' && !\App\Helpers\Auth::can('settings.backup')) continue;
            ?>
            <div class="card mb-3">
                <div class="card-header">
                    <i class="bi bi-<?= $grpIcon ?> me-2 text-primary"></i><?= $grpLabel ?>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                    <?php foreach ($grouped[$grpKey] as $s):
                        $inputType = match ($s['data_type']) {
                            'boolean' => 'checkbox',
                            'integer', 'decimal' => 'number',
                            default   => 'text',
                        };
                        $label = ucwords(str_replace('_', ' ', str_replace($grpKey.'_', '', $s['setting_key'])));
                        $isSmtp = str_contains($s['setting_key'], 'smtp_pass');
                    ?>
                        <div class="col-sm-6">
                            <label class="form-label"><?= htmlspecialchars($label) ?></label>
                            <?php if ($inputType === 'checkbox'): ?>
                                <div class="form-check form-switch mt-1">
                                    <input class="form-check-input" type="checkbox"
                                           name="settings[<?= $s['setting_key'] ?>]"
                                           value="1" <?= $s['setting_value'] ? 'checked' : '' ?>>
                                    <input type="hidden" name="_cbguard_<?= $s['setting_key'] ?>" value="0">
                                </div>
                            <?php elseif ($isSmtp): ?>
                                <input type="password"
                                       name="settings[<?= $s['setting_key'] ?>]"
                                       class="form-control form-control-sm"
                                       value="<?= htmlspecialchars($s['setting_value'] ?? '') ?>"
                                       autocomplete="new-password">
                            <?php else: ?>
                                <input type="<?= $inputType ?>"
                                       name="settings[<?= $s['setting_key'] ?>]"
                                       class="form-control form-control-sm"
                                       value="<?= htmlspecialchars($s['setting_value'] ?? '') ?>"
                                       <?= $s['data_type'] === 'decimal' ? 'step="0.01"' : '' ?>>
                            <?php endif; ?>
                            <?php if ($s['description']): ?>
                            <div class="form-text"><?= htmlspecialchars($s['description']) ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>

            <?php if (\App\Helpers\Auth::can('settings.edit')): ?>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i>Save Settings
                </button>
            </div>
            <?php endif; ?>
        </form>
    </div>

    <!-- Backup Panel -->
    <div class="col-12 col-lg-4">
        <?php if (\App\Helpers\Auth::can('settings.backup')): ?>
        <div class="card mb-3">
            <div class="card-header"><i class="bi bi-hdd me-2 text-primary"></i>Database Backup</div>
            <div class="card-body">
                <p class="text-muted small mb-3">Create a full SQL dump of the database. Backups are stored in <code>storage/backups/</code>.</p>
                <form method="POST" action="<?= APP_URL ?>/settings/backup" id="backupForm">
                    <?= \App\Helpers\CSRF::field() ?>
                    <button type="button" class="btn btn-success w-100" onclick="runBackup()">
                        <i class="bi bi-hdd-fill me-1"></i>Create Backup Now
                    </button>
                </form>
                <div id="backupResult" class="mt-2"></div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><i class="bi bi-clock-history me-2 text-primary"></i>Recent Backups</div>
            <div class="list-group list-group-flush" style="max-height:350px;overflow-y:auto">
            <?php if (empty($backups)): ?>
                <div class="list-group-item text-muted small text-center py-3">No backups yet.</div>
            <?php else: ?>
                <?php foreach ($backups as $b): ?>
                <div class="list-group-item py-2 small">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fw-medium text-truncate" style="max-width:180px"><?= htmlspecialchars($b['file_name']) ?></div>
                            <div class="text-muted" style="font-size:.7rem">
                                <?= date('d/m/Y H:i', strtotime($b['created_at'])) ?>
                                <?php if ($b['file_size']): ?> &nbsp;·&nbsp; <?= round($b['file_size']/1024, 1) ?> KB<?php endif; ?>
                                &nbsp;·&nbsp; <?= ucfirst($b['backup_type']) ?>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-1">
                            <span class="badge text-bg-<?= $b['status']==='success'?'success':($b['status']==='failed'?'danger':'warning') ?>">
                                <?= ucfirst($b['status']) ?>
                            </span>
                            <?php if ($b['status'] === 'success' && file_exists($b['file_path'])): ?>
                            <a href="<?= APP_URL ?>/settings/backup/<?= $b['id'] ?>/download" class="btn btn-xs btn-outline-secondary" title="Download">
                                <i class="bi bi-download"></i>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>.btn-xs{padding:.2rem .45rem;font-size:.75rem;}</style>
<script>
async function runBackup() {
    const btn = document.querySelector('#backupForm button');
    const origText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Creating backup…';

    try {
        const res = await App.fetch(`${APP_URL}/settings/backup`, {
            method: 'POST',
            body: { _csrf_token: CSRF_TOKEN }
        });
        document.getElementById('backupResult').innerHTML =
            `<div class="alert alert-success py-2 small mt-2">
                <i class="bi bi-check-circle me-1"></i>
                Backup created: <strong>${res.data.filename}</strong> (${res.data.size_kb} KB)
            </div>`;
        App.toast('Backup created successfully.', 'success');
        setTimeout(() => location.reload(), 2000);
    } catch(e) {
        document.getElementById('backupResult').innerHTML =
            `<div class="alert alert-danger py-2 small mt-2">${e.message}</div>`;
        App.toast(e.message, 'danger');
    } finally {
        btn.disabled = false;
        btn.innerHTML = origText;
    }
}
</script>

<?php /* notifications/index.php */ ?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <div><h1 class="page-title">Notifications</h1><p class="page-subtitle mb-0">System alerts and updates</p></div>
    <button class="btn btn-sm btn-outline-secondary" onclick="markAll()">
        <i class="bi bi-check2-all me-1"></i>Mark All Read
    </button>
</div>

<div class="card">
    <div class="list-group list-group-flush">
    <?php if (empty($notifications)): ?>
        <div class="list-group-item text-center text-muted py-5">
            <i class="bi bi-bell-slash d-block fs-2 mb-2 opacity-25"></i>
            No notifications.
        </div>
    <?php else: ?>
        <?php
        $icons = ['warning'=>'exclamation-triangle text-warning','danger'=>'x-circle text-danger',
                  'success'=>'check-circle text-success','info'=>'info-circle text-info'];
        foreach ($notifications as $n):
            [$ico] = explode(' ', $icons[$n['severity']] ?? 'bell text-muted');
        ?>
        <div class="list-group-item d-flex gap-3 py-3 <?= !$n['is_read']?'bg-primary bg-opacity-5':'' ?>">
            <i class="bi bi-<?= $icons[$n['severity']] ?? 'bell text-muted' ?> fs-5 mt-1 flex-shrink-0"></i>
            <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="fw-semibold small"><?= htmlspecialchars($n['title']) ?></div>
                    <div class="text-muted" style="font-size:.72rem;white-space:nowrap"><?= date('d/m/Y H:i',strtotime($n['created_at'])) ?></div>
                </div>
                <div class="text-muted small"><?= htmlspecialchars($n['message']) ?></div>
                <?php if (!$n['is_read']): ?>
                <button class="btn btn-link btn-sm p-0 text-decoration-none small mt-1"
                        onclick="markRead(<?= $n['id'] ?>,this)">Mark as read</button>
                <?php else: ?>
                <span class="text-muted" style="font-size:.7rem"><i class="bi bi-check2"></i> Read</span>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
    </div>
</div>

<script>
async function markRead(id, btn) {
    await App.fetch(`${APP_URL}/notifications/${id}/read`, { method: 'POST' });
    btn.closest('.list-group-item').classList.remove('bg-primary','bg-opacity-5');
    btn.remove();
}
async function markAll() {
    await App.fetch(`${APP_URL}/notifications/read-all`, { method: 'POST' });
    location.reload();
}
</script>

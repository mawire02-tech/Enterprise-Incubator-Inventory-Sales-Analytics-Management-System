<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\Auth;
use App\Helpers\Session;
use App\Services\BackupService;
use App\Services\AuditService;

class SettingsController extends Controller
{
    private BackupService $backup;
    private AuditService  $audit;

    public function __construct()
    {
        parent::__construct();
        $this->backup = new BackupService();
        $this->audit  = new AuditService();
    }

    // ─── System Settings ─────────────────────────────────────
    public function index(): void
    {
        $this->requirePermission('settings.view');

        $settings = $this->db->fetchAll(
            "SELECT * FROM system_settings ORDER BY group_name, setting_key"
        );
        $grouped = [];
        foreach ($settings as $s) {
            $grouped[$s['group_name'] ?: 'general'][] = $s;
        }

        $backups = $this->backup->listBackups();

        $this->view('settings.index', [
            'pageTitle' => 'System Settings',
            'grouped'   => $grouped,
            'backups'   => $backups,
        ]);
    }

    public function update(): void
    {
        $this->requirePermission('settings.edit');
        $this->validateCsrf();

        $updates = $this->post('settings', []);
        if (!is_array($updates)) {
            Session::flash('error', 'Invalid settings data.');
            $this->redirectRoute('/settings');
            return;
        }

        foreach ($updates as $key => $value) {
            $key = preg_replace('/[^a-z0-9_]/', '', strtolower($key));
            if (!$key) continue;

            $this->db->execute(
                "UPDATE system_settings SET setting_value = ?, updated_by = ? WHERE setting_key = ?",
                [$value, Auth::id(), $key]
            );
        }

        $this->audit->log('settings_updated', 'settings', 'System settings updated.', [], $updates, null, null, 'medium');
        Session::flash('success', 'Settings saved successfully.');
        $this->redirectRoute('/settings');
    }

    // ─── Backup ──────────────────────────────────────────────
    public function runBackup(): void
    {
        $this->requirePermission('settings.backup');
        $this->validateCsrf();

        try {
            $result = $this->backup->createBackup('manual');
            $size   = round($result['size'] / 1024, 1);
            $this->audit->log('backup_created', 'settings',
                "Manual backup created: {$result['filename']} ({$size} KB)", [], [], null, null, 'low');

            if ($this->isAjax()) {
                $this->success([
                    'filename' => $result['filename'],
                    'size_kb'  => $size,
                ], "Backup created: {$result['filename']}");
                return;
            }
            Session::flash('success', "Backup created: {$result['filename']} ({$size} KB)");
        } catch (\Throwable $e) {
            if ($this->isAjax()) { $this->error($e->getMessage()); return; }
            Session::flash('error', 'Backup failed: ' . $e->getMessage());
        }

        $this->redirectRoute('/settings');
    }

    // ─── Download backup file ────────────────────────────────
    public function downloadBackup(array $params): void
    {
        $this->requirePermission('settings.backup');

        $log = $this->backup->getLog((int)$params['id']);
        if (!$log || !file_exists($log['file_path'])) {
            http_response_code(404);
            echo 'Backup file not found.';
            return;
        }

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($log['file_path']) . '"');
        header('Content-Length: ' . filesize($log['file_path']));
        readfile($log['file_path']);
        exit;
    }
}

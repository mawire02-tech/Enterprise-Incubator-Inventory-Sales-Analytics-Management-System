<?php

namespace App\Services;

use App\Core\Database;
use App\Helpers\Auth;

/**
 * NotificationService
 * Creates and manages system notifications.
 */
class NotificationService
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ─── Create notifications ───────────────────────────────

    public function create(array $data): int
    {
        return (int)$this->db->execute(
            "INSERT INTO notifications
                (type, title, message, severity, icon, reference_type, reference_id,
                 target_role, target_user, expires_at)
             VALUES (?,?,?,?,?,?,?,?,?,?)",
            [
                $data['type'],
                $data['title'],
                $data['message'],
                $data['severity'] ?? 'info',
                $data['icon'] ?? null,
                $data['reference_type'] ?? null,
                $data['reference_id'] ?? null,
                $data['target_role'] ?? null,
                $data['target_user'] ?? null,
                $data['expires_at'] ?? null,
            ]
        );
    }

    public function notifyLowStock(int $productId, string $productName, int $currentQty, int $threshold): void
    {
        // Avoid duplicate low-stock alerts within 1 hour
        $existing = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM notifications
             WHERE type = 'low_stock' AND reference_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)",
            [$productId]
        );
        if ($existing) return;

        $this->create([
            'type'           => 'low_stock',
            'title'          => 'Low Stock Alert',
            'message'        => "'{$productName}' is running low ({$currentQty} units remaining, threshold: {$threshold}).",
            'severity'       => 'warning',
            'icon'           => 'bi-exclamation-triangle',
            'reference_type' => 'product',
            'reference_id'   => $productId,
            'target_role'    => null, // all roles
        ]);
    }

    public function notifyOutOfStock(int $productId, string $productName): void
    {
        $this->create([
            'type'           => 'out_of_stock',
            'title'          => 'Out of Stock',
            'message'        => "'{$productName}' is now OUT OF STOCK. Please replenish immediately.",
            'severity'       => 'danger',
            'icon'           => 'bi-x-circle',
            'reference_type' => 'product',
            'reference_id'   => $productId,
            'target_role'    => null,
        ]);
    }

    public function notifyNewStock(int $batchId, string $batchCode, string $productName, int $qty): void
    {
        $this->create([
            'type'           => 'new_stock',
            'title'          => 'New Stock Received',
            'message'        => "{$qty} units of '{$productName}' received (Batch: {$batchCode}).",
            'severity'       => 'success',
            'icon'           => 'bi-box-arrow-in-down',
            'reference_type' => 'batch',
            'reference_id'   => $batchId,
        ]);
    }

    public function notifySaleCompleted(int $saleId, string $invoiceNumber, float $total): void
    {
        $this->create([
            'type'           => 'sale_completed',
            'title'          => 'Sale Completed',
            'message'        => "Invoice {$invoiceNumber} completed. Total: $" . number_format($total, 2),
            'severity'       => 'success',
            'icon'           => 'bi-receipt',
            'reference_type' => 'sale',
            'reference_id'   => $saleId,
        ]);
    }

    public function notifyHighValueSale(int $saleId, string $invoiceNumber, float $total, float $threshold = 500): void
    {
        if ($total < $threshold) return;

        $this->create([
            'type'           => 'high_value_sale',
            'title'          => 'High-Value Transaction',
            'message'        => "High-value sale: {$invoiceNumber} for $" . number_format($total, 2),
            'severity'       => 'info',
            'icon'           => 'bi-cash-coin',
            'reference_type' => 'sale',
            'reference_id'   => $saleId,
            'target_role'    => 1, // admin
        ]);
    }

    public function notifyFailedLogin(string $username, int $attempts): void
    {
        $this->create([
            'type'      => 'failed_login',
            'title'     => 'Failed Login Attempt',
            'message'   => "Failed login attempt #{$attempts} for username '{$username}' from " . Auth::getClientIp(),
            'severity'  => $attempts >= 3 ? 'danger' : 'warning',
            'icon'      => 'bi-shield-exclamation',
            'target_role' => 1, // admin only
        ]);
    }

    public function notifyAccountLocked(string $username): void
    {
        $this->create([
            'type'     => 'account_locked',
            'title'    => 'Account Locked',
            'message'  => "Account '{$username}' has been locked after too many failed login attempts.",
            'severity' => 'danger',
            'icon'     => 'bi-lock',
            'target_role' => 1,
        ]);
    }

    // ─── Read / fetch notifications ─────────────────────────

    public function getForUser(int $userId, int $roleId, bool $unreadOnly = false, int $limit = 30): array
    {
        $unreadFilter = $unreadOnly ? 'AND n.is_read = 0' : '';

        return $this->db->fetchAll(
            "SELECT * FROM notifications n
             WHERE (n.target_user = ? OR n.target_role = ? OR (n.target_user IS NULL AND n.target_role IS NULL))
             {$unreadFilter}
             AND (n.expires_at IS NULL OR n.expires_at > NOW())
             ORDER BY n.created_at DESC
             LIMIT ?",
            [$userId, $roleId, $limit]
        );
    }

    public function getUnreadCount(int $userId, int $roleId): int
    {
        return (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM notifications
             WHERE (target_user = ? OR target_role = ? OR (target_user IS NULL AND target_role IS NULL))
             AND is_read = 0
             AND (expires_at IS NULL OR expires_at > NOW())",
            [$userId, $roleId]
        );
    }

    public function markRead(int $notificationId, int $userId): void
    {
        $this->db->execute(
            "UPDATE notifications SET is_read = 1, read_at = NOW(), read_by = ? WHERE id = ?",
            [$userId, $notificationId]
        );
    }

    public function markAllRead(int $userId, int $roleId): void
    {
        $this->db->execute(
            "UPDATE notifications SET is_read = 1, read_at = NOW(), read_by = ?
             WHERE (target_user = ? OR target_role = ? OR (target_user IS NULL AND target_role IS NULL))
             AND is_read = 0",
            [$userId, $userId, $roleId]
        );
    }

    public function getAll(int $page = 1, int $perPage = 50): array
    {
        $offset = ($page - 1) * $perPage;
        $total  = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM notifications");

        $rows = $this->db->fetchAll(
            "SELECT * FROM notifications ORDER BY created_at DESC LIMIT ? OFFSET ?",
            [$perPage, $offset]
        );

        return ['data' => $rows, 'total' => $total, 'page' => $page,
                'per_page' => $perPage, 'pages' => (int)ceil($total / $perPage)];
    }
}

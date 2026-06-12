<?php

namespace App\Services;

use App\Core\Database;
use App\Helpers\Auth;

/**
 * AuditService
 * Logs all significant system events.
 */
class AuditService
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function log(
        string  $action,
        string  $module,
        string  $description,
        array   $oldValues    = [],
        array   $newValues    = [],
        ?string $refType      = null,
        ?int    $refId        = null,
        string  $severity     = 'low'
    ): void {
        try {
            $this->db->execute(
                "INSERT INTO audit_logs
                    (user_id, username, action, module, description,
                     old_values, new_values, ip_address, user_agent,
                     device_type, session_id, reference_type, reference_id, severity)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
                [
                    Auth::id(),
                    Auth::user()['username'] ?? 'system',
                    $action,
                    $module,
                    $description,
                    $oldValues ? json_encode($oldValues) : null,
                    $newValues ? json_encode($newValues) : null,
                    Auth::getClientIp(),
                    Auth::getUserAgent(),
                    Auth::getDeviceType(),
                    session_id() ?: null,
                    $refType,
                    $refId,
                    $severity,
                ]
            );
        } catch (\Throwable $e) {
            // Audit failure must never crash the main app
            \App\Core\Logger::error('AuditService::log failed: ' . $e->getMessage());
        }
    }

    // ─── Convenience shortcuts ──────────────────────────────

    public function logLogin(int $userId, string $username): void
    {
        $this->db->execute(
            "INSERT INTO audit_logs (user_id, username, action, module, description, ip_address, user_agent, device_type, severity)
             VALUES (?,?,?,?,?,?,?,?,?)",
            [$userId, $username, 'login', 'auth', "User '{$username}' logged in.",
             Auth::getClientIp(), Auth::getUserAgent(), Auth::getDeviceType(), 'low']
        );
    }

    public function logFailedLogin(string $username): void
    {
        $this->db->execute(
            "INSERT INTO audit_logs (user_id, username, action, module, description, ip_address, user_agent, device_type, severity)
             VALUES (NULL,?,?,?,?,?,?,?,?)",
            [$username, 'login_failed', 'auth', "Failed login attempt for '{$username}'.",
             Auth::getClientIp(), Auth::getUserAgent(), Auth::getDeviceType(), 'medium']
        );
    }

    public function logLogout(int $userId, string $username): void
    {
        $this->db->execute(
            "INSERT INTO audit_logs (user_id, username, action, module, description, ip_address, severity)
             VALUES (?,?,?,?,?,?,?)",
            [$userId, $username, 'logout', 'auth', "User '{$username}' logged out.",
             Auth::getClientIp(), 'low']
        );
    }

    public function logSale(int $saleId, string $invoiceNumber, float $total): void
    {
        $this->log('sale_created', 'sales', "Sale {$invoiceNumber} created. Total: $" . number_format($total, 2),
            [], ['invoice' => $invoiceNumber, 'total' => $total], 'sale', $saleId, 'low');
    }

    public function logStockReceived(int $batchId, string $batchCode, int $qty, string $productName): void
    {
        $this->log('stock_received', 'inventory',
            "Stock received: {$qty} units of '{$productName}' (Batch: {$batchCode})",
            [], ['batch' => $batchCode, 'quantity' => $qty, 'product' => $productName],
            'batch', $batchId, 'low');
    }

    public function logStockAdjustment(int $batchId, string $batchCode, int $before, int $after, string $reason): void
    {
        $this->log('stock_adjusted', 'inventory',
            "Stock adjusted for batch {$batchCode}: {$before} → {$after}. Reason: {$reason}",
            ['quantity' => $before], ['quantity' => $after], 'batch', $batchId, 'medium');
    }

    public function logUserAction(string $action, int $targetUserId, string $targetUsername): void
    {
        $this->log($action, 'users',
            ucwords(str_replace('_', ' ', $action)) . " on user '{$targetUsername}'",
            [], ['target_user_id' => $targetUserId], 'user', $targetUserId, 'high');
    }

    // ─── Query helpers for Audit Module ─────────────────────

    public function getRecent(int $limit = 50): array
    {
        return $this->db->fetchAll(
            "SELECT al.*, u.full_name
             FROM audit_logs al
             LEFT JOIN users u ON u.id = al.user_id
             ORDER BY al.created_at DESC
             LIMIT ?",
            [$limit]
        );
    }

    public function search(array $filters, int $page = 1, int $perPage = 50): array
    {
        $where  = ['1=1'];
        $params = [];

        if (!empty($filters['user_id'])) {
            $where[]  = 'al.user_id = ?';
            $params[] = $filters['user_id'];
        }
        if (!empty($filters['module'])) {
            $where[]  = 'al.module = ?';
            $params[] = $filters['module'];
        }
        if (!empty($filters['action'])) {
            $where[]  = 'al.action LIKE ?';
            $params[] = '%' . $filters['action'] . '%';
        }
        if (!empty($filters['severity'])) {
            $where[]  = 'al.severity = ?';
            $params[] = $filters['severity'];
        }
        if (!empty($filters['date_from'])) {
            $where[]  = 'DATE(al.created_at) >= ?';
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where[]  = 'DATE(al.created_at) <= ?';
            $params[] = $filters['date_to'];
        }
        if (!empty($filters['search'])) {
            $where[]  = '(al.description LIKE ? OR al.username LIKE ?)';
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
        }

        $whereSql = implode(' AND ', $where);
        $offset   = ($page - 1) * $perPage;

        $total = (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM audit_logs al WHERE {$whereSql}", $params
        );

        $rows = $this->db->fetchAll(
            "SELECT al.*, u.full_name FROM audit_logs al
             LEFT JOIN users u ON u.id = al.user_id
             WHERE {$whereSql}
             ORDER BY al.created_at DESC
             LIMIT ? OFFSET ?",
            [...$params, $perPage, $offset]
        );

        return ['data' => $rows, 'total' => $total, 'page' => $page, 'per_page' => $perPage,
                'pages' => (int)ceil($total / $perPage)];
    }
}

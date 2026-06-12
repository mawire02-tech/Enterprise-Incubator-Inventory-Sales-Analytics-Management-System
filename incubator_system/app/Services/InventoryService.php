<?php

namespace App\Services;

use App\Core\Database;
use App\Helpers\Auth;

/**
 * InventoryService
 * Handles all inventory business logic: receive, adjust, deplete, archive batches.
 */
class InventoryService
{
    private Database $db;
    private NotificationService $notifications;
    private AuditService $audit;

    public function __construct()
    {
        $this->db            = Database::getInstance();
        $this->notifications = new NotificationService();
        $this->audit         = new AuditService();
    }

    // ─── Receive stock ──────────────────────────────────────

    public function receiveBatch(array $data): array
    {
        return $this->db->transaction(function ($db) use ($data) {
            // Generate batch code
            $batchCode = $this->generateBatchCode();

            $batchId = (int)$db->execute(
                "INSERT INTO inventory_batches
                    (product_id, batch_code, supplier_name, supplier_ref,
                     quantity_received, quantity_current, cost_price, selling_price,
                     acquisition_date, expiry_date, notes, status, created_by)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)",
                [
                    $data['product_id'],
                    $batchCode,
                    $data['supplier_name'] ?? null,
                    $data['supplier_ref']  ?? null,
                    $data['quantity'],
                    $data['quantity'],
                    $data['cost_price'],
                    $data['selling_price'],
                    $data['acquisition_date'],
                    $data['expiry_date'] ?? null,
                    $data['notes'] ?? null,
                    'active',
                    Auth::id(),
                ]
            );
            $batchId = (int)$db->lastInsertId();

            // Log movement
            $this->logMovement([
                'batch_id'        => $batchId,
                'product_id'      => $data['product_id'],
                'movement_type'   => 'receive',
                'quantity_before' => 0,
                'quantity_change' => $data['quantity'],
                'quantity_after'  => $data['quantity'],
                'unit_cost'       => $data['cost_price'],
                'unit_price'      => $data['selling_price'],
                'notes'           => 'Initial stock receipt',
            ]);

            // Get product name for notifications
            $product = $db->fetchOne("SELECT name FROM products WHERE id = ?", [$data['product_id']]);
            $productName = $product['name'] ?? 'Unknown';

            // Notifications & audit
            $this->notifications->notifyNewStock($batchId, $batchCode, $productName, $data['quantity']);
            $this->audit->logStockReceived($batchId, $batchCode, $data['quantity'], $productName);

            return ['batch_id' => $batchId, 'batch_code' => $batchCode];
        });
    }

    // ─── Deduct stock (used by SalesService) ────────────────

    public function deductStock(int $batchId, int $productId, int $quantity, float $unitCost, float $unitPrice, string $refType = 'sale', int $refId = 0): void
    {
        $batch = $this->db->fetchOne("SELECT * FROM inventory_batches WHERE id = ? FOR UPDATE", [$batchId]);

        if (!$batch) {
            throw new \RuntimeException("Batch #{$batchId} not found.");
        }
        if ($batch['quantity_current'] < $quantity) {
            throw new \RuntimeException("Insufficient stock in batch #{$batchId}. Available: {$batch['quantity_current']}, Required: {$quantity}");
        }

        $before = (int)$batch['quantity_current'];
        $after  = $before - $quantity;

        $this->db->execute(
            "UPDATE inventory_batches SET
                quantity_current = quantity_current - ?,
                quantity_sold    = quantity_sold + ?,
                total_revenue    = total_revenue + ?,
                total_cost       = total_cost + ?,
                gross_profit     = gross_profit + ?
             WHERE id = ?",
            [$quantity, $quantity, $unitPrice * $quantity, $unitCost * $quantity,
             ($unitPrice - $unitCost) * $quantity, $batchId]
        );

        $this->logMovement([
            'batch_id'        => $batchId,
            'product_id'      => $productId,
            'movement_type'   => 'sale',
            'reference_type'  => $refType,
            'reference_id'    => $refId,
            'quantity_before' => $before,
            'quantity_change' => -$quantity,
            'quantity_after'  => $after,
            'unit_cost'       => $unitCost,
            'unit_price'      => $unitPrice,
        ]);

        // Check if batch depleted
        if ($after === 0) {
            $this->depleteBatch($batchId);
        }

        // Check low stock
        $this->checkLowStock($productId);
    }

    // ─── Return stock ───────────────────────────────────────

    public function returnStock(int $batchId, int $productId, int $quantity, float $unitCost, float $unitPrice, int $refId): void
    {
        $before = (int)$this->db->fetchColumn(
            "SELECT quantity_current FROM inventory_batches WHERE id = ?", [$batchId]
        );

        $this->db->execute(
            "UPDATE inventory_batches SET
                quantity_current = quantity_current + ?,
                quantity_sold    = quantity_sold - ?,
                total_revenue    = total_revenue - ?,
                total_cost       = total_cost - ?,
                gross_profit     = gross_profit - ?,
                status           = IF(status = 'depleted', 'active', status)
             WHERE id = ?",
            [$quantity, $quantity, $unitPrice * $quantity, $unitCost * $quantity,
             ($unitPrice - $unitCost) * $quantity, $batchId]
        );

        $this->logMovement([
            'batch_id'        => $batchId,
            'product_id'      => $productId,
            'movement_type'   => 'return',
            'reference_type'  => 'return',
            'reference_id'    => $refId,
            'quantity_before' => $before,
            'quantity_change' => $quantity,
            'quantity_after'  => $before + $quantity,
            'unit_cost'       => $unitCost,
            'unit_price'      => $unitPrice,
        ]);
    }

    // ─── Stock adjustment ────────────────────────────────────

    public function adjustStock(array $data): int
    {
        return $this->db->transaction(function ($db) use ($data) {
            $batch = $db->fetchOne(
                "SELECT ib.*, p.name AS product_name FROM inventory_batches ib
                 JOIN products p ON p.id = ib.product_id WHERE ib.id = ? FOR UPDATE",
                [$data['batch_id']]
            );

            if (!$batch) throw new \RuntimeException("Batch not found.");

            $change = 0;
            $before = (int)$batch['quantity_current'];

            switch ($data['adjustment_type']) {
                case 'add':
                    $change = abs((int)$data['quantity']);
                    break;
                case 'remove':
                case 'damage':
                    $change = -abs((int)$data['quantity']);
                    break;
                case 'correction':
                    $change = (int)$data['quantity'] - $before;
                    break;
            }

            $after = max(0, $before + $change);

            // Update damage count separately
            if ($data['adjustment_type'] === 'damage') {
                $db->execute(
                    "UPDATE inventory_batches SET quantity_current = ?, quantity_damaged = quantity_damaged + ?,
                     quantity_adjusted = quantity_adjusted + ?,
                     loss_amount = loss_amount + ? WHERE id = ?",
                    [$after, abs($change), $change, abs($change) * $batch['cost_price'], $data['batch_id']]
                );
            } else {
                $db->execute(
                    "UPDATE inventory_batches SET quantity_current = ?, quantity_adjusted = quantity_adjusted + ? WHERE id = ?",
                    [$after, $change, $data['batch_id']]
                );
            }

            // Insert adjustment record
            $adjCode = 'ADJ-' . strtoupper(uniqid());
            $db->execute(
                "INSERT INTO stock_adjustments
                    (adjustment_code, batch_id, product_id, adjustment_type,
                     quantity_before, quantity_change, quantity_after, reason, notes, adjusted_by)
                 VALUES (?,?,?,?,?,?,?,?,?,?)",
                [$adjCode, $data['batch_id'], $batch['product_id'], $data['adjustment_type'],
                 $before, $change, $after, $data['reason'], $data['notes'] ?? null, Auth::id()]
            );
            $adjId = (int)$db->lastInsertId();

            $this->logMovement([
                'batch_id'        => $data['batch_id'],
                'product_id'      => $batch['product_id'],
                'movement_type'   => $data['adjustment_type'] === 'damage' ? 'damage' : 'adjustment',
                'reference_type'  => 'adjustment',
                'reference_id'    => $adjId,
                'quantity_before' => $before,
                'quantity_change' => $change,
                'quantity_after'  => $after,
            ]);

            $this->audit->logStockAdjustment($data['batch_id'], $batch['batch_code'], $before, $after, $data['reason']);
            $this->checkLowStock($batch['product_id']);

            if ($after === 0) $this->depleteBatch($data['batch_id']);

            return $adjId;
        });
    }

    // ─── Batch depletion & archival ─────────────────────────

    public function depleteBatch(int $batchId): void
    {
        $this->db->execute(
            "UPDATE inventory_batches SET status = 'depleted', depleted_at = NOW() WHERE id = ?",
            [$batchId]
        );
    }

    public function archiveBatch(int $batchId): array
    {
        return $this->db->transaction(function ($db) use ($batchId) {
            $batch = $db->fetchOne("SELECT ib.*, p.name AS product_name FROM inventory_batches ib
                                    JOIN products p ON p.id = ib.product_id WHERE ib.id = ?", [$batchId]);
            if (!$batch) throw new \RuntimeException("Batch not found.");

            // Calculate final financials
            $revenue   = (float)$batch['total_revenue'];
            $cost      = (float)$batch['total_cost'];
            $lossAmt   = (float)$batch['loss_amount'];
            $gross     = $revenue - $cost;
            $net       = $gross - $lossAmt;

            $db->execute(
                "UPDATE inventory_batches SET
                    status = 'archived', archived_at = NOW(), archived_by = ?,
                    gross_profit = ?, net_profit = ?
                 WHERE id = ?",
                [Auth::id(), $gross, $net, $batchId]
            );

            $this->audit->log('batch_archived', 'inventory',
                "Batch {$batch['batch_code']} archived. Revenue: $" . number_format($revenue, 2)
                . " | Net Profit: $" . number_format($net, 2),
                [], [], 'batch', $batchId, 'low');

            return ['batch_id' => $batchId, 'revenue' => $revenue, 'cost' => $cost,
                    'gross_profit' => $gross, 'net_profit' => $net, 'loss' => $lossAmt];
        });
    }

    // ─── Low stock check ─────────────────────────────────────

    public function checkLowStock(int $productId): void
    {
        $product = $this->db->fetchOne(
            "SELECT p.*, COALESCE(SUM(ib.quantity_current), 0) AS total_stock
             FROM products p
             LEFT JOIN inventory_batches ib ON ib.product_id = p.id AND ib.status = 'active'
             WHERE p.id = ?
             GROUP BY p.id",
            [$productId]
        );

        if (!$product) return;

        $total = (int)$product['total_stock'];

        if ($total === 0) {
            $this->notifications->notifyOutOfStock($productId, $product['name']);
        } elseif ($total <= $product['low_stock_threshold']) {
            $this->notifications->notifyLowStock($productId, $product['name'], $total, $product['low_stock_threshold']);
        }
    }

    // ─── Inventory summary for dashboard ────────────────────

    public function getDashboardStats(): array
    {
        return $this->db->fetchOne(
            "SELECT
                COALESCE(SUM(ib.quantity_current), 0)                         AS total_stock,
                COALESCE(SUM(ib.quantity_current * ib.cost_price), 0)         AS stock_value_cost,
                COALESCE(SUM(ib.quantity_current * ib.selling_price), 0)      AS stock_value_retail,
                COUNT(DISTINCT ib.id)                                          AS active_batches,
                COUNT(DISTINCT ib.product_id)                                  AS products_in_stock,
                SUM(CASE WHEN ib.quantity_current <= p.low_stock_threshold
                         AND ib.quantity_current > 0 THEN 1 ELSE 0 END)      AS low_stock_items,
                SUM(CASE WHEN ib.quantity_current = 0 THEN 1 ELSE 0 END)     AS out_of_stock_items
             FROM inventory_batches ib
             JOIN products p ON p.id = ib.product_id
             WHERE ib.status = 'active'"
        ) ?: [];
    }

    // ─── Stock levels per product ────────────────────────────

    public function getStockLevels(): array
    {
        return $this->db->fetchAll(
            "SELECT p.id, p.sku, p.name, p.low_stock_threshold,
                    COALESCE(SUM(ib.quantity_current), 0) AS total_stock,
                    COALESCE(SUM(ib.quantity_current * ib.cost_price), 0)    AS value_cost,
                    COALESCE(SUM(ib.quantity_current * ib.selling_price), 0) AS value_retail,
                    COUNT(ib.id) AS batch_count
             FROM products p
             LEFT JOIN inventory_batches ib ON ib.product_id = p.id AND ib.status = 'active'
             WHERE p.is_active = 1
             GROUP BY p.id
             ORDER BY total_stock ASC"
        );
    }

    public function getBatchesForProduct(int $productId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM inventory_batches WHERE product_id = ? ORDER BY acquisition_date DESC",
            [$productId]
        );
    }

    public function getActiveBatches(int $productId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM inventory_batches WHERE product_id = ? AND status = 'active' AND quantity_current > 0
             ORDER BY acquisition_date ASC",  // FIFO
            [$productId]
        );
    }

    // ─── Movement history ────────────────────────────────────

    public function getMovements(array $filters = [], int $page = 1, int $perPage = 50): array
    {
        $where  = ['1=1'];
        $params = [];

        if (!empty($filters['batch_id'])) {
            $where[] = 'im.batch_id = ?'; $params[] = $filters['batch_id'];
        }
        if (!empty($filters['product_id'])) {
            $where[] = 'im.product_id = ?'; $params[] = $filters['product_id'];
        }
        if (!empty($filters['movement_type'])) {
            $where[] = 'im.movement_type = ?'; $params[] = $filters['movement_type'];
        }
        if (!empty($filters['date_from'])) {
            $where[] = 'DATE(im.performed_at) >= ?'; $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where[] = 'DATE(im.performed_at) <= ?'; $params[] = $filters['date_to'];
        }

        $whereSql = implode(' AND ', $where);
        $offset   = ($page - 1) * $perPage;

        $total = (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM inventory_movements im WHERE {$whereSql}", $params
        );

        $rows = $this->db->fetchAll(
            "SELECT im.*, p.name AS product_name, p.sku, ib.batch_code, u.full_name AS performed_by_name
             FROM inventory_movements im
             JOIN inventory_batches ib ON ib.id = im.batch_id
             JOIN products p ON p.id = im.product_id
             LEFT JOIN users u ON u.id = im.performed_by
             WHERE {$whereSql}
             ORDER BY im.performed_at DESC
             LIMIT ? OFFSET ?",
            [...$params, $perPage, $offset]
        );

        return ['data' => $rows, 'total' => $total, 'page' => $page, 'per_page' => $perPage,
                'pages' => (int)ceil($total / $perPage)];
    }

    // ─── Helpers ─────────────────────────────────────────────

    private function generateBatchCode(): string
    {
        $prefix = $this->db->fetchColumn(
            "SELECT setting_value FROM system_settings WHERE setting_key = 'batch_prefix'"
        ) ?: 'BATCH';

        $year  = date('Y');
        $month = date('m');
        $seq   = (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM inventory_batches WHERE batch_code LIKE ?",
            ["{$prefix}-{$year}{$month}-%"]
        ) + 1;

        return sprintf('%s-%s%s-%04d', $prefix, $year, $month, $seq);
    }

    private function logMovement(array $data): void
    {
        $this->db->execute(
            "INSERT INTO inventory_movements
                (batch_id, product_id, movement_type, reference_type, reference_id,
                 quantity_before, quantity_change, quantity_after, unit_cost, unit_price, notes, performed_by)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?)",
            [
                $data['batch_id'],
                $data['product_id'],
                $data['movement_type'],
                $data['reference_type'] ?? null,
                $data['reference_id'] ?? null,
                $data['quantity_before'],
                $data['quantity_change'],
                $data['quantity_after'],
                $data['unit_cost'] ?? null,
                $data['unit_price'] ?? null,
                $data['notes'] ?? null,
                Auth::id(),
            ]
        );
    }
}
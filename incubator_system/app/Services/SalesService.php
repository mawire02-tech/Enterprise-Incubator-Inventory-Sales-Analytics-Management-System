<?php

namespace App\Services;

use App\Core\Database;
use App\Helpers\Auth;

/**
 * SalesService
 * Handles complete sale lifecycle: creation, stock deduction, invoice, ledger, returns.
 */
class SalesService
{
    private Database $db;
    private InventoryService $inventory;
    private NotificationService $notifications;
    private AuditService $audit;
    private LedgerService $ledger;

    public function __construct()
    {
        $this->db            = Database::getInstance();
        $this->inventory     = new InventoryService();
        $this->notifications = new NotificationService();
        $this->audit         = new AuditService();
        $this->ledger        = new LedgerService();
    }

    // ─── Process a new sale ─────────────────────────────────

    public function createSale(array $data): array
    {
        return $this->db->transaction(function ($db) use ($data) {
            $invoiceNumber = $this->generateInvoiceNumber();
            $saleDate      = $data['sale_date'] ?? date('Y-m-d');

            // Calculate totals
            $subtotal       = 0.00;
            $totalCost      = 0.00;
            $lineItems      = [];

            foreach ($data['items'] as $item) {
                // Find batch (FIFO: earliest acquisition date first)
                $batch = $this->resolveBatch($item['product_id'], $item['quantity'], $item['batch_id'] ?? null);

                $unitPrice  = (float)($item['unit_price'] ?? $batch['selling_price']);
                $unitCost   = (float)$batch['cost_price'];
                $qty        = (int)$item['quantity'];
                $lineDiscount = (float)($item['discount'] ?? 0);
                $lineTotal  = ($unitPrice * $qty) - $lineDiscount;
                $lineCost   = $unitCost * $qty;

                $lineItems[] = [
                    'product_id'      => $item['product_id'],
                    'batch_id'        => $batch['id'],
                    'quantity'        => $qty,
                    'unit_cost'       => $unitCost,
                    'unit_price'      => $unitPrice,
                    'discount_amount' => $lineDiscount,
                    'line_total'      => $lineTotal,
                    'line_cost'       => $lineCost,
                    'line_profit'     => $lineTotal - $lineCost,
                ];

                $subtotal  += $lineTotal;
                $totalCost += $lineCost;
            }

            // Apply header discount
            $discountType   = $data['discount_type'] ?? 'none';
            $discountValue  = (float)($data['discount_value'] ?? 0);
            $discountAmount = match ($discountType) {
                'percent' => $subtotal * ($discountValue / 100),
                'fixed'   => min($discountValue, $subtotal),
                default   => 0.00,
            };

            $taxRate   = (float)($data['tax_rate'] ?? 0);
            $taxAmount = ($subtotal - $discountAmount) * ($taxRate / 100);
            $total     = $subtotal - $discountAmount + $taxAmount;
            $amountPaid = (float)($data['amount_paid'] ?? $total);
            $amountDue  = max(0, $total - $amountPaid);
            $paymentStatus = $amountDue <= 0 ? 'paid' : ($amountPaid > 0 ? 'partial' : 'unpaid');
            $grossProfit = $subtotal - $discountAmount - $totalCost;

            // Insert sale header
            $db->execute(
                "INSERT INTO sales
                    (invoice_number, customer_id, customer_name, customer_phone,
                     sale_date, subtotal, discount_type, discount_value, discount_amount,
                     tax_rate, tax_amount, total_amount, total_cost, gross_profit,
                     payment_method, payment_status, amount_paid, amount_due,
                     status, notes, served_by, created_by)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
                [
                    $invoiceNumber,
                    $data['customer_id'] ?? null,
                    $data['customer_name'] ?? null,
                    $data['customer_phone'] ?? null,
                    $saleDate, $subtotal,
                    $discountType, $discountValue, $discountAmount,
                    $taxRate, $taxAmount, $total, $totalCost, $grossProfit,
                    $data['payment_method'] ?? 'cash',
                    $paymentStatus, $amountPaid, $amountDue,
                    'completed', $data['notes'] ?? null,
                    Auth::id(), Auth::id(),
                ]
            );
            $saleId = (int)$db->lastInsertId();

            // Insert line items and deduct stock
            foreach ($lineItems as $item) {
                // Get product snapshot
                $product = $db->fetchOne("SELECT * FROM products WHERE id = ?", [$item['product_id']]);

                $db->execute(
                    "INSERT INTO sale_items
                        (sale_id, product_id, batch_id, quantity, unit_cost, unit_price,
                         discount_amount, line_total, line_cost, line_profit, product_snapshot)
                     VALUES (?,?,?,?,?,?,?,?,?,?,?)",
                    [
                        $saleId, $item['product_id'], $item['batch_id'], $item['quantity'],
                        $item['unit_cost'], $item['unit_price'], $item['discount_amount'],
                        $item['line_total'], $item['line_cost'], $item['line_profit'],
                        json_encode($product),
                    ]
                );

                // Deduct from inventory batch
                $this->inventory->deductStock(
                    $item['batch_id'], $item['product_id'], $item['quantity'],
                    $item['unit_cost'], $item['unit_price'], 'sale', $saleId
                );
            }

            // Update daily ledger
            $this->ledger->updateForSale($saleDate, $total, $totalCost, $grossProfit,
                array_sum(array_column($lineItems, 'quantity')),
                $data['payment_method'] ?? 'cash', $discountAmount, $taxAmount);

            // Notifications
            $this->notifications->notifySaleCompleted($saleId, $invoiceNumber, $total);
            $this->notifications->notifyHighValueSale($saleId, $invoiceNumber, $total);

            // Audit
            $this->audit->logSale($saleId, $invoiceNumber, $total);

            return [
                'sale_id'        => $saleId,
                'invoice_number' => $invoiceNumber,
                'total'          => $total,
                'gross_profit'   => $grossProfit,
                'amount_due'     => $amountDue,
            ];
        });
    }

    // ─── Process return ──────────────────────────────────────

    public function processReturn(array $data): array
    {
        return $this->db->transaction(function ($db) use ($data) {
            $sale = $db->fetchOne("SELECT * FROM sales WHERE id = ?", [$data['sale_id']]);
            if (!$sale) throw new \RuntimeException("Sale not found.");

            $returnNumber = 'RET-' . strtoupper(substr(uniqid(), -8));
            $returnAmount = 0.00;

            $db->execute(
                "INSERT INTO sale_returns (return_number, sale_id, return_date, reason, return_amount, restock, status, notes, processed_by)
                 VALUES (?,?,?,?,0,?,?,?,?)",
                [$returnNumber, $data['sale_id'], $data['return_date'] ?? date('Y-m-d'),
                 $data['reason'], (int)($data['restock'] ?? 1), 'approved', $data['notes'] ?? null, Auth::id()]
            );
            $returnId = (int)$db->lastInsertId();

            foreach ($data['items'] as $ri) {
                $saleItem = $db->fetchOne("SELECT * FROM sale_items WHERE id = ? AND sale_id = ?",
                    [$ri['sale_item_id'], $data['sale_id']]);

                if (!$saleItem) continue;

                $qty       = (int)$ri['quantity'];
                $lineTotal = $saleItem['unit_price'] * $qty;
                $returnAmount += $lineTotal;

                $db->execute(
                    "INSERT INTO return_items (return_id, sale_item_id, product_id, batch_id, quantity, unit_price, line_total)
                     VALUES (?,?,?,?,?,?,?)",
                    [$returnId, $ri['sale_item_id'], $saleItem['product_id'], $saleItem['batch_id'],
                     $qty, $saleItem['unit_price'], $lineTotal]
                );

                if ($data['restock'] ?? true) {
                    $this->inventory->returnStock(
                        $saleItem['batch_id'], $saleItem['product_id'], $qty,
                        $saleItem['unit_cost'], $saleItem['unit_price'], $returnId
                    );
                }
            }

            // Update return amount
            $db->execute("UPDATE sale_returns SET return_amount = ? WHERE id = ?", [$returnAmount, $returnId]);

            // Update original sale status
            $db->execute("UPDATE sales SET status = 'returned' WHERE id = ?", [$data['sale_id']]);

            // Update ledger
            $this->ledger->updateForReturn($sale['sale_date'], $returnAmount);

            $this->audit->log('sale_returned', 'sales',
                "Return {$returnNumber} processed for invoice {$sale['invoice_number']}. Amount: $" . number_format($returnAmount, 2),
                [], ['return_number' => $returnNumber, 'amount' => $returnAmount], 'return', $returnId, 'medium');

            return ['return_id' => $returnId, 'return_number' => $returnNumber, 'return_amount' => $returnAmount];
        });
    }

    // ─── Void a sale ─────────────────────────────────────────

    public function voidSale(int $saleId, string $reason): bool
    {
        return $this->db->transaction(function ($db) use ($saleId, $reason) {
            $sale = $db->fetchOne("SELECT * FROM sales WHERE id = ? AND status = 'completed'", [$saleId]);
            if (!$sale) throw new \RuntimeException("Sale not found or cannot be voided.");

            $items = $db->fetchAll("SELECT * FROM sale_items WHERE sale_id = ?", [$saleId]);

            foreach ($items as $item) {
                $this->inventory->returnStock(
                    $item['batch_id'], $item['product_id'], $item['quantity'],
                    $item['unit_cost'], $item['unit_price'], 0
                );
            }

            $db->execute("UPDATE sales SET status = 'voided', notes = CONCAT(COALESCE(notes,''), '\nVOIDED: {$reason}') WHERE id = ?", [$saleId]);

            // Reverse ledger
            $this->ledger->updateForReturn($sale['sale_date'], $sale['total_amount']);

            $this->audit->log('sale_voided', 'sales',
                "Sale {$sale['invoice_number']} voided. Reason: {$reason}",
                [], ['reason' => $reason], 'sale', $saleId, 'high');

            return true;
        });
    }

    // ─── Dashboard & report queries ─────────────────────────

    public function getDashboardStats(): array
    {
        return $this->db->fetchOne(
            "SELECT
                COALESCE(SUM(CASE WHEN DATE(sale_date) = CURDATE() THEN total_amount END), 0)             AS today_revenue,
                COALESCE(SUM(CASE WHEN YEARWEEK(sale_date,1) = YEARWEEK(CURDATE(),1) THEN total_amount END), 0) AS week_revenue,
                COALESCE(SUM(CASE WHEN MONTH(sale_date) = MONTH(CURDATE()) AND YEAR(sale_date) = YEAR(CURDATE()) THEN total_amount END), 0) AS month_revenue,
                COALESCE(SUM(CASE WHEN DATE(sale_date) = CURDATE() THEN gross_profit END), 0)             AS today_profit,
                COALESCE(SUM(CASE WHEN MONTH(sale_date) = MONTH(CURDATE()) AND YEAR(sale_date) = YEAR(CURDATE()) THEN gross_profit END), 0) AS month_profit,
                COUNT(CASE WHEN DATE(sale_date) = CURDATE() AND status = 'completed' THEN 1 END)          AS today_sales_count,
                COUNT(CASE WHEN status = 'completed' THEN 1 END)                                          AS total_sales
             FROM sales
             WHERE status != 'voided'"
        ) ?: [];
    }

    public function getRecentSales(int $limit = 10): array
    {
        return $this->db->fetchAll(
            "SELECT s.*, c.full_name AS customer_full_name, u.full_name AS served_by_name
             FROM sales s
             LEFT JOIN customers c ON c.id = s.customer_id
             LEFT JOIN users u ON u.id = s.served_by
             ORDER BY s.created_at DESC LIMIT ?",
            [$limit]
        );
    }

    public function getBestSellers(int $limit = 10, string $period = 'month'): array
    {
        $dateFilter = match ($period) {
            'today' => 'DATE(s.sale_date) = CURDATE()',
            'week'  => 'YEARWEEK(s.sale_date,1) = YEARWEEK(CURDATE(),1)',
            'month' => 'MONTH(s.sale_date) = MONTH(CURDATE()) AND YEAR(s.sale_date) = YEAR(CURDATE())',
            'year'  => 'YEAR(s.sale_date) = YEAR(CURDATE())',
            default => '1=1',
        };

        return $this->db->fetchAll(
            "SELECT p.name, p.sku, SUM(si.quantity) AS total_qty, SUM(si.line_total) AS total_revenue,
                    SUM(si.line_profit) AS total_profit
             FROM sale_items si
             JOIN sales s ON s.id = si.sale_id AND s.status = 'completed'
             JOIN products p ON p.id = si.product_id
             WHERE {$dateFilter}
             GROUP BY p.id
             ORDER BY total_qty DESC
             LIMIT ?",
            [$limit]
        );
    }

    public function getSalesForPeriod(string $dateFrom, string $dateTo, int $page = 1, int $perPage = 50): array
    {
        $offset = ($page - 1) * $perPage;

        $total = (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM sales WHERE sale_date BETWEEN ? AND ?", [$dateFrom, $dateTo]
        );

        $rows = $this->db->fetchAll(
            "SELECT s.*, c.full_name AS customer_full_name, u.full_name AS served_by_name
             FROM sales s
             LEFT JOIN customers c ON c.id = s.customer_id
             LEFT JOIN users u ON u.id = s.served_by
             WHERE s.sale_date BETWEEN ? AND ?
             ORDER BY s.sale_date DESC, s.id DESC
             LIMIT ? OFFSET ?",
            [$dateFrom, $dateTo, $perPage, $offset]
        );

        return ['data' => $rows, 'total' => $total, 'page' => $page, 'per_page' => $perPage,
                'pages' => (int)ceil($total / $perPage)];
    }

    public function getSaleById(int $id): ?array
    {
        $sale = $this->db->fetchOne(
            "SELECT s.*, c.full_name AS customer_full_name, c.phone AS customer_phone_full,
                    u.full_name AS served_by_name
             FROM sales s
             LEFT JOIN customers c ON c.id = s.customer_id
             LEFT JOIN users u ON u.id = s.served_by
             WHERE s.id = ?",
            [$id]
        );

        if (!$sale) return null;

        $sale['items'] = $this->db->fetchAll(
            "SELECT si.*, p.name AS product_name, p.sku, ib.batch_code
             FROM sale_items si
             JOIN products p ON p.id = si.product_id
             JOIN inventory_batches ib ON ib.id = si.batch_id
             WHERE si.sale_id = ?",
            [$id]
        );

        return $sale;
    }

    // ─── Helpers ─────────────────────────────────────────────

    private function resolveBatch(int $productId, int $qty, ?int $preferredBatchId = null): array
    {
        if ($preferredBatchId) {
            $batch = $this->db->fetchOne(
                "SELECT * FROM inventory_batches WHERE id = ? AND status = 'active' AND quantity_current >= ?",
                [$preferredBatchId, $qty]
            );
            if ($batch) return $batch;
        }

        // FIFO: oldest batch first
        $batch = $this->db->fetchOne(
            "SELECT * FROM inventory_batches
             WHERE product_id = ? AND status = 'active' AND quantity_current >= ?
             ORDER BY acquisition_date ASC
             LIMIT 1",
            [$productId, $qty]
        );

        if (!$batch) {
            throw new \RuntimeException("Insufficient stock for product #{$productId}. Required: {$qty}.");
        }

        return $batch;
    }

    private function generateInvoiceNumber(): string
    {
        $prefix = $this->db->fetchColumn(
            "SELECT setting_value FROM system_settings WHERE setting_key = 'invoice_prefix'"
        ) ?: 'INV';

        $year  = date('Y');
        $month = date('m');
        $seq   = (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM sales WHERE invoice_number LIKE ?",
            ["{$prefix}-{$year}{$month}-%"]
        ) + 1;

        return sprintf('%s-%s%s-%05d', $prefix, $year, $month, $seq);
    }
}

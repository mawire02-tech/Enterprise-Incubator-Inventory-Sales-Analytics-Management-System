<?php

namespace App\Services;

use App\Core\Database;

/**
 * ProfitLossService
 * Calculates COGS, margins, batch P&L, inventory valuation.
 */
class ProfitLossService
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getPeriodSummary(string $dateFrom, string $dateTo): array
    {
        $sales = $this->db->fetchOne(
            "SELECT
                COALESCE(SUM(total_amount), 0)    AS total_revenue,
                COALESCE(SUM(total_cost), 0)      AS total_cogs,
                COALESCE(SUM(gross_profit), 0)    AS gross_profit,
                COALESCE(SUM(discount_amount), 0) AS total_discounts,
                COALESCE(SUM(tax_amount), 0)      AS total_tax,
                COUNT(*)                          AS total_transactions
             FROM sales
             WHERE sale_date BETWEEN ? AND ? AND status NOT IN ('voided')",
            [$dateFrom, $dateTo]
        ) ?: [];

        $returns = (float)$this->db->fetchColumn(
            "SELECT COALESCE(SUM(return_amount), 0) FROM sale_returns sr
             JOIN sales s ON s.id = sr.sale_id
             WHERE s.sale_date BETWEEN ? AND ?",
            [$dateFrom, $dateTo]
        );

        $damages = (float)$this->db->fetchColumn(
            "SELECT COALESCE(SUM(ib.quantity_damaged * ib.cost_price), 0)
             FROM inventory_batches ib
             WHERE ib.acquisition_date BETWEEN ? AND ?",
            [$dateFrom, $dateTo]
        );

        $inventoryValue = $this->getInventoryValue();
        $grossProfit    = (float)($sales['gross_profit'] ?? 0) - $returns;
        $netProfit      = $grossProfit - $damages;
        $grossMargin    = (float)($sales['total_revenue'] ?? 0) > 0
            ? ($grossProfit / (float)$sales['total_revenue']) * 100 : 0;

        return [
            'total_revenue'      => (float)($sales['total_revenue'] ?? 0),
            'total_cogs'         => (float)($sales['total_cogs'] ?? 0),
            'gross_profit'       => $grossProfit,
            'net_profit'         => $netProfit,
            'gross_margin_pct'   => round($grossMargin, 2),
            'total_discounts'    => (float)($sales['total_discounts'] ?? 0),
            'total_tax'          => (float)($sales['total_tax'] ?? 0),
            'return_losses'      => $returns,
            'damage_losses'      => $damages,
            'total_losses'       => $returns + $damages,
            'total_transactions' => (int)($sales['total_transactions'] ?? 0),
            'inventory_value'    => $inventoryValue,
        ];
    }

    public function getBatchProfitability(): array
    {
        return $this->db->fetchAll(
            "SELECT ib.*, p.name AS product_name, p.sku,
                    (ib.quantity_received - ib.quantity_current) AS qty_sold,
                    (ib.total_revenue - ib.total_cost)           AS gross_profit_calc,
                    CASE WHEN ib.total_revenue > 0
                         THEN ((ib.total_revenue - ib.total_cost) / ib.total_revenue) * 100
                         ELSE 0 END AS margin_pct
             FROM inventory_batches ib
             JOIN products p ON p.id = ib.product_id
             ORDER BY ib.acquisition_date DESC"
        );
    }

    public function getInventoryValuation(): array
    {
        return $this->db->fetchAll(
            "SELECT p.id, p.sku, p.name,
                    COALESCE(SUM(ib.quantity_current), 0)                          AS total_qty,
                    COALESCE(AVG(ib.cost_price), 0)                                AS avg_cost,
                    COALESCE(AVG(ib.selling_price), 0)                             AS avg_sell,
                    COALESCE(SUM(ib.quantity_current * ib.cost_price), 0)          AS total_value_cost,
                    COALESCE(SUM(ib.quantity_current * ib.selling_price), 0)       AS total_value_retail
             FROM products p
             LEFT JOIN inventory_batches ib ON ib.product_id = p.id AND ib.status = 'active'
             WHERE p.is_active = 1
             GROUP BY p.id
             ORDER BY total_value_cost DESC"
        );
    }

    public function getInventoryValue(): float
    {
        return (float)$this->db->fetchColumn(
            "SELECT COALESCE(SUM(quantity_current * cost_price), 0)
             FROM inventory_batches WHERE status = 'active'"
        );
    }

    public function getMonthlyTrend(int $months = 12): array
    {
        return $this->db->fetchAll(
            "SELECT
                DATE_FORMAT(sale_date, '%Y-%m') AS month,
                SUM(total_amount)  AS revenue,
                SUM(total_cost)    AS cost,
                SUM(gross_profit)  AS gross_profit,
                COUNT(*)           AS transactions
             FROM sales
             WHERE sale_date >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
               AND status NOT IN ('voided')
             GROUP BY DATE_FORMAT(sale_date, '%Y-%m')
             ORDER BY month ASC",
            [$months]
        );
    }

    public function getRevenueByPaymentMethod(string $dateFrom, string $dateTo): array
    {
        return $this->db->fetchAll(
            "SELECT payment_method, SUM(total_amount) AS revenue, COUNT(*) AS transactions
             FROM sales
             WHERE sale_date BETWEEN ? AND ? AND status = 'completed'
             GROUP BY payment_method
             ORDER BY revenue DESC",
            [$dateFrom, $dateTo]
        );
    }

    public function getProductMargins(string $dateFrom, string $dateTo): array
    {
        return $this->db->fetchAll(
            "SELECT p.name, p.sku,
                    SUM(si.quantity)   AS qty_sold,
                    SUM(si.line_total) AS revenue,
                    SUM(si.line_cost)  AS cost,
                    SUM(si.line_profit)AS profit,
                    CASE WHEN SUM(si.line_total) > 0
                         THEN (SUM(si.line_profit) / SUM(si.line_total)) * 100
                         ELSE 0 END AS margin_pct
             FROM sale_items si
             JOIN sales s ON s.id = si.sale_id
             JOIN products p ON p.id = si.product_id
             WHERE s.sale_date BETWEEN ? AND ? AND s.status = 'completed'
             GROUP BY p.id
             ORDER BY profit DESC",
            [$dateFrom, $dateTo]
        );
    }
}

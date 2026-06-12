<?php

namespace App\Services;

use App\Core\Database;

/**
 * ExportService
 * Generates CSV and basic Excel (TSV) exports for reports.
 */
class ExportService
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function export(string $type, string $format, string $from, string $to): string
    {
        $data = match ($type) {
            'sales'      => $this->getSalesData($from, $to),
            'inventory'  => $this->getInventoryData(),
            'customers'  => $this->getCustomersData(),
            'ledger'     => $this->getLedgerData($from, $to),
            'movements'  => $this->getMovementsData($from, $to),
            default      => throw new \InvalidArgumentException("Unknown export type: {$type}"),
        };

        $filename = "{$type}_{$from}_{$to}_" . date('His') . ".{$format}";
        $filepath = EXPORTS_PATH . '/' . $filename;

        match ($format) {
            'csv'  => $this->writeCsv($filepath, $data),
            'xlsx' => $this->writeCsv($filepath, $data), // fallback to CSV; use PhpSpreadsheet for real xlsx
            default => throw new \InvalidArgumentException("Unsupported format: {$format}"),
        };

        return $filepath;
    }

    private function writeCsv(string $path, array $data): void
    {
        if (empty($data)) {
            file_put_contents($path, '');
            return;
        }

        $fh = fopen($path, 'w');
        // BOM for Excel UTF-8
        fwrite($fh, "\xEF\xBB\xBF");
        // Headers
        fputcsv($fh, array_keys($data[0]));
        foreach ($data as $row) {
            fputcsv($fh, array_values($row));
        }
        fclose($fh);
    }

    private function getSalesData(string $from, string $to): array
    {
        return $this->db->fetchAll(
            "SELECT s.invoice_number AS 'Invoice', s.sale_date AS 'Date',
                    COALESCE(c.full_name, s.customer_name, 'Walk-in') AS 'Customer',
                    s.subtotal AS 'Subtotal', s.discount_amount AS 'Discount',
                    s.tax_amount AS 'Tax', s.total_amount AS 'Total',
                    s.total_cost AS 'Cost', s.gross_profit AS 'Profit',
                    s.payment_method AS 'Payment', s.payment_status AS 'Status',
                    u.full_name AS 'Served By'
             FROM sales s
             LEFT JOIN customers c ON c.id = s.customer_id
             LEFT JOIN users u ON u.id = s.served_by
             WHERE s.sale_date BETWEEN ? AND ?
             ORDER BY s.sale_date DESC",
            [$from, $to]
        );
    }

    private function getInventoryData(): array
    {
        return $this->db->fetchAll(
            "SELECT p.sku AS 'SKU', p.name AS 'Product', p.category AS 'Category',
                    ib.batch_code AS 'Batch', ib.quantity_received AS 'Received',
                    ib.quantity_current AS 'Current', ib.quantity_sold AS 'Sold',
                    ib.quantity_damaged AS 'Damaged', ib.cost_price AS 'Cost Price',
                    ib.selling_price AS 'Sell Price', ib.acquisition_date AS 'Date',
                    ib.supplier_name AS 'Supplier', ib.status AS 'Status'
             FROM inventory_batches ib
             JOIN products p ON p.id = ib.product_id
             ORDER BY ib.acquisition_date DESC"
        );
    }

    private function getCustomersData(): array
    {
        return $this->db->fetchAll(
            "SELECT c.customer_code AS 'Code', c.full_name AS 'Name',
                    c.email AS 'Email', c.phone AS 'Phone', c.city AS 'City',
                    COUNT(s.id) AS 'Orders', COALESCE(SUM(s.total_amount),0) AS 'Lifetime Value',
                    MAX(s.sale_date) AS 'Last Purchase', c.created_at AS 'Member Since'
             FROM customers c
             LEFT JOIN sales s ON s.customer_id = c.id AND s.status='completed'
             WHERE c.deleted_at IS NULL
             GROUP BY c.id
             ORDER BY c.full_name"
        );
    }

    private function getLedgerData(string $from, string $to): array
    {
        return $this->db->fetchAll(
            "SELECT ledger_date AS 'Date', total_transactions AS 'Transactions',
                    total_units_sold AS 'Units Sold', total_revenue AS 'Revenue',
                    total_inventory_cost AS 'COGS', gross_profit AS 'Gross Profit',
                    total_returns AS 'Returns', net_profit AS 'Net Profit',
                    total_discounts AS 'Discounts', cash_sales AS 'Cash',
                    ecocash_sales AS 'EcoCash/OneMoney', other_sales AS 'Other'
             FROM daily_ledger
             WHERE ledger_date BETWEEN ? AND ?
             ORDER BY ledger_date ASC",
            [$from, $to]
        );
    }

    private function getMovementsData(string $from, string $to): array
    {
        return $this->db->fetchAll(
            "SELECT im.performed_at AS 'Date', p.name AS 'Product', p.sku AS 'SKU',
                    ib.batch_code AS 'Batch', im.movement_type AS 'Type',
                    im.quantity_before AS 'Before', im.quantity_change AS 'Change',
                    im.quantity_after AS 'After', im.unit_cost AS 'Unit Cost',
                    im.unit_price AS 'Unit Price', im.notes AS 'Notes',
                    u.full_name AS 'Performed By'
             FROM inventory_movements im
             JOIN inventory_batches ib ON ib.id = im.batch_id
             JOIN products p ON p.id = im.product_id
             LEFT JOIN users u ON u.id = im.performed_by
             WHERE DATE(im.performed_at) BETWEEN ? AND ?
             ORDER BY im.performed_at DESC",
            [$from, $to]
        );
    }
}

<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Services\SalesService;
use App\Services\InventoryService;
use App\Services\LedgerService;
use App\Services\ProfitLossService;
use App\Services\AuditService;

// ─── ReportsController ───────────────────────────────────────────────────────
class ReportsController extends Controller
{
    private SalesService $sales;
    private InventoryService $inventory;
    private LedgerService $ledger;
    private ProfitLossService $pnl;

    public function __construct()
    {
        parent::__construct();
        $this->sales     = new SalesService();
        $this->inventory = new InventoryService();
        $this->ledger    = new LedgerService();
        $this->pnl       = new ProfitLossService();
    }

    public function index(): void
    {
        $this->requirePermission('reports.view');
        $this->view('reports.index', ['pageTitle' => 'Reports']);
    }

    public function sales(): void
    {
        $this->requirePermission('reports.view');

        $from    = $this->get('from', date('Y-m-01'));
        $to      = $this->get('to', date('Y-m-d'));
        $groupBy = $this->get('group', 'day');

        $groupExpr = match ($groupBy) {
            'week'  => "DATE_FORMAT(sale_date,'%Y-W%u')",
            'month' => "DATE_FORMAT(sale_date,'%Y-%m')",
            'year'  => "YEAR(sale_date)",
            default => "sale_date",
        };

        $grouped = $this->db->fetchAll(
            "SELECT {$groupExpr} AS period,
                    COUNT(*)             AS transactions,
                    SUM(total_amount)    AS revenue,
                    SUM(total_cost)      AS cost,
                    SUM(gross_profit)    AS gross_profit,
                    SUM(discount_amount) AS discounts
             FROM sales
             WHERE sale_date BETWEEN ? AND ? AND status != 'voided'
             GROUP BY period ORDER BY period ASC",
            [$from, $to]
        );

        $summary = $this->pnl->getPeriodSummary($from, $to);
        $byMethod = $this->pnl->getRevenueByPaymentMethod($from, $to);
        $bestSellers = $this->sales->getBestSellers(10, 'all');

        $this->view('reports.sales', [
            'pageTitle'   => 'Sales Report',
            'from'        => $from,
            'to'          => $to,
            'groupBy'     => $groupBy,
            'grouped'     => $grouped,
            'summary'     => $summary,
            'byMethod'    => $byMethod,
            'bestSellers' => $bestSellers,
        ]);
    }

    public function inventory(): void
    {
        $this->requirePermission('reports.view');

        $stockLevels  = $this->inventory->getStockLevels();
        $valuation    = $this->pnl->getInventoryValuation();
        $invStats     = $this->inventory->getDashboardStats();
        $movements    = $this->inventory->getMovements(['date_from' => date('Y-m-01'), 'date_to' => date('Y-m-d')]);

        $this->view('reports.inventory', [
            'pageTitle'   => 'Inventory Report',
            'stockLevels' => $stockLevels,
            'valuation'   => $valuation,
            'invStats'    => $invStats,
            'movements'   => $movements,
        ]);
    }

    public function pnl(): void
    {
        $this->requirePermission('reports.financial');

        $from    = $this->get('from', date('Y-m-01'));
        $to      = $this->get('to', date('Y-m-d'));
        $summary = $this->pnl->getPeriodSummary($from, $to);
        $monthly = $this->pnl->getMonthlyTrend(12);
        $margins = $this->pnl->getProductMargins($from, $to);
        $batches = $this->pnl->getBatchProfitability();

        $this->view('reports.pnl', [
            'pageTitle' => 'Profit & Loss Report',
            'from'      => $from,
            'to'        => $to,
            'summary'   => $summary,
            'monthly'   => $monthly,
            'margins'   => $margins,
            'batches'   => $batches,
        ]);
    }

    public function ledger(): void
    {
        $this->requirePermission('reports.view');

        $from  = $this->get('from', date('Y-m-01'));
        $to    = $this->get('to', date('Y-m-d'));
        $rows  = $this->ledger->getRange($from, $to);
        $totals = $this->ledger->getSummary('month');

        $this->view('reports.ledger', [
            'pageTitle' => 'Daily Sales Ledger',
            'from'      => $from,
            'to'        => $to,
            'rows'      => $rows,
            'totals'    => $totals,
        ]);
    }

    // AJAX: export triggers (returns file path for download)
    public function export(): void
    {
        $this->requirePermission('reports.export');

        $type   = $this->get('type', 'sales');
        $format = $this->get('format', 'csv');
        $from   = $this->get('from', date('Y-m-01'));
        $to     = $this->get('to', date('Y-m-d'));

        $exporter = new \App\Services\ExportService();
        try {
            $filePath = $exporter->export($type, $format, $from, $to);
            $this->success(['file' => basename($filePath), 'url' => APP_URL . '/reports/download?f=' . basename($filePath)]);
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
        }
    }

    public function download(): void
    {
        $this->requirePermission('reports.export');
        $file = basename($this->get('f', ''));
        $path = EXPORTS_PATH . '/' . $file;

        if (!$file || !file_exists($path)) { http_response_code(404); echo 'File not found.'; return; }

        $ext = pathinfo($file, PATHINFO_EXTENSION);
        $mimes = ['csv' => 'text/csv', 'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'pdf' => 'application/pdf'];

        header('Content-Type: ' . ($mimes[$ext] ?? 'application/octet-stream'));
        header('Content-Disposition: attachment; filename="' . $file . '"');
        header('Content-Length: ' . filesize($path));
        readfile($path);
    }
}

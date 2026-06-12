<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\Auth;
use App\Helpers\Session;
use App\Helpers\Validator;
use App\Services\SalesService;
use App\Services\AuditService;

class SalesController extends Controller
{
    private SalesService $sales;
    private AuditService $audit;

    public function __construct()
    {
        parent::__construct();
        $this->sales = new SalesService();
        $this->audit = new AuditService();
    }

    // ─── Sales list ──────────────────────────────────────────
    public function index(): void
    {
        $this->requirePermission('sales.view');

        $from    = $this->get('from', date('Y-m-01'));
        $to      = $this->get('to', date('Y-m-d'));
        $status  = $this->get('status', '');
        $search  = $this->get('q', '');
        $page    = (int)$this->get('page', 1);

        $where  = ['s.sale_date BETWEEN ? AND ?'];
        $params = [$from, $to];

        if ($status) { $where[] = 's.status = ?'; $params[] = $status; }
        if ($search) {
            $where[]  = '(s.invoice_number LIKE ? OR c.full_name LIKE ? OR s.customer_name LIKE ?)';
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        $whereSql = implode(' AND ', $where);
        $total    = (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM sales s LEFT JOIN customers c ON c.id=s.customer_id WHERE {$whereSql}", $params
        );
        $paginate = $this->paginate($total, PER_PAGE_DEFAULT);
        $offset   = $paginate['offset'];

        $list = $this->db->fetchAll(
            "SELECT s.*, c.full_name AS customer_full_name, u.full_name AS served_by_name
             FROM sales s
             LEFT JOIN customers c ON c.id = s.customer_id
             LEFT JOIN users u ON u.id = s.served_by
             WHERE {$whereSql}
             ORDER BY s.sale_date DESC, s.id DESC
             LIMIT ? OFFSET ?",
            [...$params, $paginate['per_page'], $offset]
        );

        // Period totals — only pass profit figures to roles that may see them
        $totals = null;
        if (Auth::can('sales.view_profit')) {
            $totals = $this->db->fetchOne(
                "SELECT SUM(s.total_amount) AS total_revenue, SUM(s.gross_profit) AS total_profit,
                        COUNT(*) AS count
                 FROM sales s WHERE {$whereSql} AND s.status = 'completed'",
                $params
            );
        } else {
            // Provide transaction count only — no revenue or profit
            $totals = $this->db->fetchOne(
                "SELECT COUNT(*) AS count
                 FROM sales s WHERE {$whereSql} AND s.status = 'completed'",
                $params
            );
        }

        $this->view('sales.index', [
            'pageTitle'       => 'Sales',
            'list'            => $list,
            'from'            => $from,
            'to'              => $to,
            'status'          => $status,
            'search'          => $search,
            'paginate'        => $paginate,
            'totals'          => $totals,
            'canViewProfit'   => Auth::can('sales.view_profit'),
        ]);
    }

    // ─── POS / New sale ──────────────────────────────────────
    public function create(): void
    {
        $this->requirePermission('sales.create');

        $products = $this->db->fetchAll(
            "SELECT p.id, p.sku, p.name, p.unit, p.category,
                    COALESCE(SUM(ib.quantity_current),0) AS stock_qty,
                    MIN(ib.selling_price) AS min_price,
                    MAX(ib.selling_price) AS max_price
             FROM products p
             LEFT JOIN inventory_batches ib ON ib.product_id = p.id AND ib.status='active'
             WHERE p.is_active = 1
             GROUP BY p.id
             HAVING stock_qty > 0
             ORDER BY p.name"
        );

        $customers = $this->db->fetchAll(
            "SELECT id, customer_code, full_name, phone FROM customers WHERE is_active = 1 ORDER BY full_name"
        );

        $settings = $this->db->fetchAll(
            "SELECT setting_key, setting_value FROM system_settings WHERE setting_key IN ('tax_rate','currency_symbol')"
        );
        $settingsMap = array_column($settings, 'setting_value', 'setting_key');

        $this->view('sales.create', [
            'pageTitle' => 'New Sale',
            'products'  => $products,
            'customers' => $customers,
            'tax_rate'  => (float)($settingsMap['tax_rate'] ?? 0),
            'currency'  => $settingsMap['currency_symbol'] ?? '$',
        ]);
    }

    // ─── AJAX: Get product batch price & availability ────────
    public function getProductBatches(array $params): void
    {
        $this->requirePermission('sales.create');

        $productId = (int)$params['id'];
        $batches   = $this->db->fetchAll(
            "SELECT id, batch_code, quantity_current, cost_price, selling_price, acquisition_date
             FROM inventory_batches
             WHERE product_id = ? AND status = 'active' AND quantity_current > 0
             ORDER BY acquisition_date ASC",
            [$productId]
        );

        // Hide cost price from non-privileged users
        if (!Auth::can('inventory.view_cost')) {
            foreach ($batches as &$b) unset($b['cost_price']);
        }

        $this->success($batches);
    }

    // ─── Store sale ──────────────────────────────────────────
    public function store(): void
    {
        $this->requirePermission('sales.create');
        $this->validateCsrf();

        $body = $this->isJson() ? $this->jsonBody() : $this->all();

        $v = Validator::make($body, [
            'payment_method' => 'required|in:cash,ecocash,onemoney,bank_transfer,card,credit',
        ]);

        if (empty($body['items']) || !is_array($body['items'])) {
            $this->error('No items in sale.');
            return;
        }

        if ($v->fails()) {
            $this->error(implode(' ', array_merge(...array_values($v->errors()))));
            return;
        }

        try {
            $result = $this->sales->createSale($body);
            $this->success($result, 'Sale completed successfully.');
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
        }
    }

    // ─── View single sale / invoice ──────────────────────────
    public function show(array $params): void
    {
        $this->requirePermission('sales.view');

        $sale = $this->sales->getSaleById((int)$params['id']);
        if (!$sale) {
            Session::flash('error', 'Sale not found.');
            $this->redirectRoute('/sales');
            return;
        }

        // Strip profit data from sale record for roles without permission
        if (!Auth::can('sales.view_profit')) {
            unset($sale['gross_profit'], $sale['total_cost'], $sale['cost_price']);
            // Also strip line-item cost/profit if present
            if (!empty($sale['items'])) {
                foreach ($sale['items'] as &$item) {
                    unset($item['cost_price'], $item['gross_profit'], $item['total_cost']);
                }
            }
        }

        $this->view('sales.show', [
            'pageTitle'     => 'Invoice ' . $sale['invoice_number'],
            'sale'          => $sale,
            'canViewProfit' => Auth::can('sales.view_profit'),
        ]);
    }

    // ─── Print invoice (no layout) ───────────────────────────
    public function printInvoice(array $params): void
    {
        $this->requirePermission('sales.view');

        $sale = $this->sales->getSaleById((int)$params['id']);
        if (!$sale) { http_response_code(404); echo 'Not found'; return; }

        // Strip profit data for roles without permission
        if (!Auth::can('sales.view_profit')) {
            unset($sale['gross_profit'], $sale['total_cost']);
            if (!empty($sale['items'])) {
                foreach ($sale['items'] as &$item) {
                    unset($item['cost_price'], $item['gross_profit'], $item['total_cost']);
                }
            }
        }

        $company = $this->db->fetchAll(
            "SELECT setting_key, setting_value FROM system_settings WHERE group_name='company'"
        );
        $co = array_column($company, 'setting_value', 'setting_key');

        $this->view('sales.print_invoice', ['sale' => $sale, 'company' => $co], null);
    }

    // ─── Void sale ───────────────────────────────────────────
    public function void(array $params): void
    {
        $this->requirePermission('sales.void');
        $this->validateCsrf();

        $reason = trim($this->post('reason', ''));
        if (!$reason) {
            $this->error('A reason is required to void a sale.');
            return;
        }

        try {
            $this->sales->voidSale((int)$params['id'], $reason);
            $this->success(null, 'Sale voided successfully.');
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
        }
    }

    // ─── Return form ──────────────────────────────────────────
    public function showReturn(array $params): void
    {
        $this->requirePermission('sales.return');

        $sale = $this->sales->getSaleById((int)$params['id']);
        if (!$sale || $sale['status'] !== 'completed') {
            Session::flash('error', 'Sale not found or cannot be returned.');
            $this->redirectRoute('/sales');
            return;
        }

        $this->view('sales.return', ['pageTitle' => 'Process Return – ' . $sale['invoice_number'], 'sale' => $sale]);
    }

    public function storeReturn(array $params): void
    {
        $this->requirePermission('sales.return');
        $this->validateCsrf();

        $body = $this->isJson() ? $this->jsonBody() : $this->all();
        $body['sale_id'] = (int)$params['id'];

        if (empty($body['items']) || empty($body['reason'])) {
            $this->error('Items and reason are required.');
            return;
        }

        try {
            $result = $this->sales->processReturn($body);
            $this->success($result, 'Return processed successfully.');
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
        }
    }

    // ─── AJAX: Recent sales feed ─────────────────────────────
    public function recent(): void
    {
        $this->requirePermission('sales.view');
        $this->success($this->sales->getRecentSales(15));
    }

    // ─── AJAX: Daily summary ─────────────────────────────────
    public function dailySummary(): void
    {
        $this->requirePermission('sales.view');
        $date   = $this->get('date', date('Y-m-d'));
        $ledger = (new \App\Services\LedgerService())->getForDate($date);

        // Strip financial fields for roles without permission
        if ($ledger && !Auth::can('sales.view_profit')) {
            unset($ledger['gross_profit'], $ledger['net_profit'],
                  $ledger['total_inventory_cost'], $ledger['total_revenue']);
        }

        $this->success($ledger);
    }
}

<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\Auth;
use App\Services\InventoryService;
use App\Services\SalesService;
use App\Services\LedgerService;
use App\Services\ProfitLossService;
use App\Services\NotificationService;
use App\Services\AuditService;

class DashboardController extends Controller
{
    private InventoryService $inventory;
    private SalesService $sales;
    private LedgerService $ledger;
    private ProfitLossService $pnl;
    private NotificationService $notifications;
    private AuditService $audit;

    public function __construct()
    {
        parent::__construct();
        $this->inventory     = new InventoryService();
        $this->sales         = new SalesService();
        $this->ledger        = new LedgerService();
        $this->pnl           = new ProfitLossService();
        $this->notifications = new NotificationService();
        $this->audit         = new AuditService();
    }

    // ─── Main dashboard ──────────────────────────────────────
    public function index(): void
    {
        $user        = Auth::user();
        $role        = Auth::role();
        $invStats    = $this->inventory->getDashboardStats();
        $saleStats   = $this->sales->getDashboardStats();
        $stockLevels = $this->inventory->getStockLevels();
        $unreadCount = $this->notifications->getUnreadCount($user['id'], $user['role_id']);

        // Low stock / out-of-stock always available (needed by stock clerk & manager)
        $lowStockItems = array_values(array_filter(
            $stockLevels,
            fn($p) => $p['total_stock'] > 0 && $p['total_stock'] <= $p['low_stock_threshold']
        ));
        $outOfStockItems = array_values(array_filter(
            $stockLevels,
            fn($p) => $p['total_stock'] == 0
        ));

        // Base view data available to every role
        $viewData = [
            'pageTitle'       => 'Dashboard',
            'invStats'        => $invStats,
            'stockLevels'     => $stockLevels,
            'lowStockItems'   => $lowStockItems,
            'outOfStockItems' => $outOfStockItems,
            'unreadCount'     => $unreadCount,
            // Financial flags — views must check these before rendering profit/revenue widgets
            'canViewFinancials' => Auth::can('reports.financial') || Auth::can('pnl.view'),
            'canViewProfit'     => Auth::can('sales.view_profit'),
        ];

        // ── Sales Officer & Manager: show sales data but strip profit from Sales role ──
        if ($role !== 'stock_clerk') {
            $viewData['saleStats']   = $saleStats;
            $viewData['recentSales'] = $this->sales->getRecentSales(8);
            $viewData['bestSellers'] = $this->sales->getBestSellers(5, 'month');
            $viewData['totalCustomers'] = (int)$this->db->fetchColumn(
                "SELECT COUNT(*) FROM customers WHERE is_active=1 AND deleted_at IS NULL"
            );
        }

        // ── Financial data: admin & manager only ─────────────────────────────────────
        // Sales Officer and Stock Clerk must NOT see revenue/profit trend or ledger
        if (Auth::can('reports.financial') || Auth::can('pnl.view')) {
            $viewData['todayLedger']  = $this->ledger->getForDate(date('Y-m-d'));
            $viewData['monthlyTrend'] = $this->pnl->getMonthlyTrend(6);
        } else {
            $viewData['todayLedger']  = null;
            $viewData['monthlyTrend'] = [];
        }

        // ── Audit feed: only roles with audit.view permission ────────────────────────
        if (Auth::can('audit.view')) {
            $viewData['recentAudit'] = $this->audit->getRecent(10);
        } else {
            $viewData['recentAudit'] = [];
        }

        $this->view('dashboard.index', $viewData);
    }

    // ─── AJAX: Live KPI update (SSE / polling) ───────────────
    public function liveStats(): void
    {
        $invStats  = $this->inventory->getDashboardStats();
        $user      = Auth::user();
        $unread    = $this->notifications->getUnreadCount($user['id'], $user['role_id']);

        $payload = [
            'inventory' => $invStats,
            'unread'    => $unread,
            'timestamp' => date('Y-m-d H:i:s'),
        ];

        // Only send sales/profit data to roles that may see it
        if (Auth::role() !== 'stock_clerk') {
            $saleStats = $this->sales->getDashboardStats();

            // Strip profit figures from sales officer
            if (!Auth::can('sales.view_profit')) {
                unset($saleStats['month_profit']);
                unset($saleStats['today_profit']);
            }

            $payload['sales'] = $saleStats;
        }

        $this->success($payload);
    }

    // ─── SSE endpoint: real-time activity feed ───────────────
    public function activityStream(): void
    {
        @ini_set('output_buffering', 'off');
        @ini_set('zlib.output_compression', false);
        while (ob_get_level()) ob_end_flush();

        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no');

        $user      = Auth::user();
        $role      = Auth::role();
        $lastCheck = time();

        while (true) {
            if (connection_aborted()) break;

            $unread   = $this->notifications->getUnreadCount($user['id'], $user['role_id']);
            $invStats = $this->inventory->getDashboardStats();

            $payload = [
                'unread'    => $unread,
                'inventory' => $invStats,
                'ts'        => time(),
            ];

            if ($role !== 'stock_clerk') {
                $saleStats = $this->sales->getDashboardStats();
                if (!Auth::can('sales.view_profit')) {
                    unset($saleStats['month_profit']);
                    unset($saleStats['today_profit']);
                }
                $payload['sales'] = $saleStats;
            }

            if (Auth::can('audit.view')) {
                $payload['activities'] = $this->audit->getRecent(5);
            }

            echo "data: " . json_encode($payload) . "\n\n";
            flush();

            sleep(SSE_RETRY_MS / 1000);

            if (time() - $lastCheck > 300) break;
        }
    }

    // ─── Chart data ──────────────────────────────────────────
    public function chartData(): void
    {
        $type = $this->get('type', 'revenue');

        // Revenue/daily/payment charts contain financial data — restrict
        if (in_array($type, ['revenue', 'daily', 'payment_methods'])
            && !Auth::can('reports.financial')
            && !Auth::can('pnl.view')
        ) {
            $this->error('Access denied.');
            return;
        }

        switch ($type) {
            case 'revenue':
                $data = $this->pnl->getMonthlyTrend(12);
                $this->success($data);
                break;

            case 'daily':
                $from = date('Y-m-d', strtotime('-30 days'));
                $to   = date('Y-m-d');
                $data = $this->ledger->getRange($from, $to);
                $this->success($data);
                break;

            case 'products':
                // Best sellers (no financial detail) — available to sales & above
                if (!Auth::can('sales.view')) {
                    $this->error('Access denied.');
                    return;
                }
                $data = $this->sales->getBestSellers(10, 'month');
                $this->success($data);
                break;

            case 'payment_methods':
                $from = date('Y-m-01');
                $to   = date('Y-m-d');
                $data = $this->pnl->getRevenueByPaymentMethod($from, $to);
                $this->success($data);
                break;

            default:
                $this->error('Unknown chart type.');
        }
    }
}

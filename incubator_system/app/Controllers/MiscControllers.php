<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\Auth;
use App\Helpers\Session;
use App\Services\AuditService;
use App\Services\NotificationService;
use App\Services\ProfitLossService;

// ─── AuditController ─────────────────────────────────────────────────────────
class AuditController extends Controller
{
    private AuditService $audit;

    public function __construct()
    {
        parent::__construct();
        $this->audit = new AuditService();
    }

    public function index(): void
    {
        $this->requirePermission('audit.view');

        $filters = [
            'module'    => $this->get('module', ''),
            'severity'  => $this->get('severity', ''),
            'date_from' => $this->get('from', date('Y-m-d', strtotime('-7 days'))),
            'date_to'   => $this->get('to', date('Y-m-d')),
            'search'    => $this->get('q', ''),
            'user_id'   => (int)$this->get('user_id', 0) ?: null,
        ];

        $page   = (int)$this->get('page', 1);
        $result = $this->audit->search($filters, $page, 50);

        $modules = $this->db->fetchAll("SELECT DISTINCT module FROM audit_logs ORDER BY module");
        $users   = $this->db->fetchAll("SELECT id, username, full_name FROM users WHERE deleted_at IS NULL ORDER BY full_name");

        $this->view('audit.index', [
            'pageTitle' => 'Audit Log',
            'result'    => $result,
            'filters'   => $filters,
            'modules'   => $modules,
            'users'     => $users,
        ]);
    }

    public function show(array $params): void
    {
        $this->requirePermission('audit.view');
        $log = $this->db->fetchOne(
            "SELECT al.*, u.full_name FROM audit_logs al LEFT JOIN users u ON u.id=al.user_id WHERE al.id=?",
            [$params['id']]
        );
        if (!$log) { http_response_code(404); echo 'Not found'; return; }
        $this->view('audit.show', ['pageTitle' => 'Audit Entry #' . $log['id'], 'log' => $log]);
    }

    public function export(): void
    {
        $this->requirePermission('audit.export');

        $filters = [
            'date_from' => $this->get('from', date('Y-m-d', strtotime('-30 days'))),
            'date_to'   => $this->get('to', date('Y-m-d')),
        ];
        $result = $this->audit->search($filters, 1, 10000);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="audit_log_' . date('Ymd') . '.csv"');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['ID','User','Action','Module','Description','IP','Device','Severity','Date']);
        foreach ($result['data'] as $row) {
            fputcsv($out, [
                $row['id'], $row['username'], $row['action'], $row['module'],
                $row['description'], $row['ip_address'], $row['device_type'],
                $row['severity'], $row['created_at'],
            ]);
        }
        fclose($out);
        exit;
    }

    // AJAX: live feed for dashboard
    public function feed(): void
    {
        $this->requirePermission('audit.view');
        $this->success($this->audit->getRecent(20));
    }
}


// ─── NotificationController ──────────────────────────────────────────────────
class NotificationController extends Controller
{
    private NotificationService $notifications;

    public function __construct()
    {
        parent::__construct();
        $this->notifications = new NotificationService();
    }

    public function index(): void
    {
        $user   = Auth::user();
        $result = $this->notifications->getAll();
        $this->view('notifications.index', [
            'pageTitle'     => 'Notifications',
            'notifications' => $result['data'],
            'paginate'      => $result,
        ]);
    }

    public function unread(): void
    {
        $user  = Auth::user();
        $items = $this->notifications->getForUser($user['id'], $user['role_id'], true, 20);
        $count = $this->notifications->getUnreadCount($user['id'], $user['role_id']);
        $this->success(['items' => $items, 'count' => $count]);
    }

    public function markRead(array $params): void
    {
        $this->notifications->markRead((int)$params['id'], Auth::id());
        $this->success(null, 'Marked as read.');
    }

    public function markAllRead(): void
    {
        $user = Auth::user();
        $this->notifications->markAllRead($user['id'], $user['role_id']);
        $this->success(null, 'All notifications marked as read.');
    }
}


// ─── PnLController ───────────────────────────────────────────────────────────
class PnLController extends Controller
{
    private ProfitLossService $pnl;

    public function __construct()
    {
        parent::__construct();
        $this->pnl = new ProfitLossService();
    }

    public function index(): void
    {
        $this->requirePermission('pnl.view');

        $from    = $this->get('from', date('Y-m-01'));
        $to      = $this->get('to', date('Y-m-d'));
        $summary = $this->pnl->getPeriodSummary($from, $to);
        $monthly = $this->pnl->getMonthlyTrend(12);
        $batches = $this->pnl->getBatchProfitability();
        $margins = $this->pnl->getProductMargins($from, $to);
        $valuation = $this->pnl->getInventoryValuation();

        $this->view('reports.pnl', [
            'pageTitle' => 'Profit & Loss',
            'from'      => $from,
            'to'        => $to,
            'summary'   => $summary,
            'monthly'   => $monthly,
            'batches'   => $batches,
            'margins'   => $margins,
            'valuation' => $valuation,
        ]);
    }

    public function api(): void
    {
        $this->requirePermission('pnl.view');
        $from = $this->get('from', date('Y-m-01'));
        $to   = $this->get('to', date('Y-m-d'));
        $this->success([
            'summary'   => $this->pnl->getPeriodSummary($from, $to),
            'monthly'   => $this->pnl->getMonthlyTrend(6),
            'valuation' => $this->pnl->getInventoryValue(),
        ]);
    }
}


// ─── CalculatorController ────────────────────────────────────────────────────
class CalculatorController extends Controller
{
    public function index(): void
    {
        $this->view('calculator.index', ['pageTitle' => 'Business Calculator']);
    }

    // AJAX: perform server-side calculations
    public function calculate(): void
    {
        $type = $this->post('type', '');
        $d    = $this->isJson() ? $this->jsonBody() : $this->all();

        $result = match ($type) {
            'profit_margin'   => $this->calcProfitMargin($d),
            'markup'          => $this->calcMarkup($d),
            'discount'        => $this->calcDiscount($d),
            'break_even'      => $this->calcBreakEven($d),
            'inventory_value' => $this->calcInventoryValue($d),
            'revenue_forecast'=> $this->calcForecast($d),
            default           => ['error' => 'Unknown calculation type.'],
        };

        $this->success($result);
    }

    private function calcProfitMargin(array $d): array
    {
        $revenue = (float)($d['revenue'] ?? 0);
        $cost    = (float)($d['cost'] ?? 0);
        if ($revenue <= 0) return ['error' => 'Revenue must be greater than zero.'];
        $gross  = $revenue - $cost;
        return [
            'gross_profit'   => round($gross, 2),
            'gross_margin'   => round(($gross / $revenue) * 100, 2),
            'net_profit'     => round($gross, 2),
            'markup_pct'     => $cost > 0 ? round(($gross / $cost) * 100, 2) : 0,
        ];
    }

    private function calcMarkup(array $d): array
    {
        $cost   = (float)($d['cost'] ?? 0);
        $markup = (float)($d['markup_pct'] ?? 0);
        if ($cost <= 0) return ['error' => 'Cost must be greater than zero.'];
        $sell   = $cost * (1 + $markup / 100);
        return [
            'selling_price'  => round($sell, 2),
            'profit_amount'  => round($sell - $cost, 2),
            'profit_margin'  => round((($sell - $cost) / $sell) * 100, 2),
        ];
    }

    private function calcDiscount(array $d): array
    {
        $price    = (float)($d['price'] ?? 0);
        $discount = (float)($d['discount_pct'] ?? 0);
        $discAmt  = $price * ($discount / 100);
        return [
            'discount_amount' => round($discAmt, 2),
            'final_price'     => round($price - $discAmt, 2),
            'savings'         => round($discAmt, 2),
        ];
    }

    private function calcBreakEven(array $d): array
    {
        $fixed   = (float)($d['fixed_cost'] ?? 0);
        $price   = (float)($d['price'] ?? 0);
        $varCost = (float)($d['variable_cost'] ?? 0);
        $contrib = $price - $varCost;
        if ($contrib <= 0) return ['error' => 'Price must exceed variable cost.'];
        $units   = ceil($fixed / $contrib);
        return [
            'break_even_units'    => $units,
            'break_even_revenue'  => round($units * $price, 2),
            'contribution_margin' => round($contrib, 2),
            'contribution_ratio'  => round(($contrib / $price) * 100, 2),
        ];
    }

    private function calcInventoryValue(array $d): array
    {
        $qty   = (float)($d['quantity'] ?? 0);
        $cost  = (float)($d['cost_price'] ?? 0);
        $sell  = (float)($d['selling_price'] ?? 0);
        return [
            'total_cost_value'   => round($qty * $cost, 2),
            'total_retail_value' => round($qty * $sell, 2),
            'potential_profit'   => round($qty * ($sell - $cost), 2),
            'margin_pct'         => $sell > 0 ? round((($sell - $cost) / $sell) * 100, 2) : 0,
        ];
    }

    private function calcForecast(array $d): array
    {
        $avgDaily  = (float)($d['avg_daily_revenue'] ?? 0);
        $growthPct = (float)($d['growth_pct'] ?? 0);
        return [
            'weekly'   => round($avgDaily * 7 * (1 + $growthPct / 100), 2),
            'monthly'  => round($avgDaily * 30 * (1 + $growthPct / 100), 2),
            'quarterly'=> round($avgDaily * 90 * (1 + $growthPct / 100), 2),
            'annual'   => round($avgDaily * 365 * (1 + $growthPct / 100), 2),
        ];
    }
}

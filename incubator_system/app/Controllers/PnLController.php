<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\ProfitLossService;

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

        $from      = $this->get('from', date('Y-m-01'));
        $to        = $this->get('to', date('Y-m-d'));
        $summary   = $this->pnl->getPeriodSummary($from, $to);
        $monthly   = $this->pnl->getMonthlyTrend(12);
        $batches   = $this->pnl->getBatchProfitability();
        $margins   = $this->pnl->getProductMargins($from, $to);
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

<?php

namespace App\Controllers;

use App\Core\Controller;

class CalculatorController extends Controller
{
    public function index(): void
    {
        $this->view('calculator.index', ['pageTitle' => 'Business Calculator']);
    }

    public function calculate(): void
    {
        $type = $this->post('type', '');
        $d    = $this->isJson() ? $this->jsonBody() : $this->all();

        $result = match ($type) {
            'profit_margin'    => $this->calcProfitMargin($d),
            'markup'           => $this->calcMarkup($d),
            'discount'         => $this->calcDiscount($d),
            'break_even'       => $this->calcBreakEven($d),
            'inventory_value'  => $this->calcInventoryValue($d),
            'revenue_forecast' => $this->calcForecast($d),
            default            => ['error' => 'Unknown calculation type.'],
        };

        $this->success($result);
    }

    private function calcProfitMargin(array $d): array
    {
        $revenue = (float)($d['revenue'] ?? 0);
        $cost    = (float)($d['cost'] ?? 0);
        if ($revenue <= 0) return ['error' => 'Revenue must be greater than zero.'];
        $gross = $revenue - $cost;
        return [
            'gross_profit' => round($gross, 2),
            'gross_margin' => round(($gross / $revenue) * 100, 2),
            'net_profit'   => round($gross, 2),
            'markup_pct'   => $cost > 0 ? round(($gross / $cost) * 100, 2) : 0,
        ];
    }

    private function calcMarkup(array $d): array
    {
        $cost   = (float)($d['cost'] ?? 0);
        $markup = (float)($d['markup_pct'] ?? 0);
        if ($cost <= 0) return ['error' => 'Cost must be greater than zero.'];
        $sell = $cost * (1 + $markup / 100);
        return [
            'selling_price' => round($sell, 2),
            'profit_amount' => round($sell - $cost, 2),
            'profit_margin' => round((($sell - $cost) / $sell) * 100, 2),
        ];
    }

    private function calcDiscount(array $d): array
    {
        $price   = (float)($d['price'] ?? 0);
        $discPct = (float)($d['discount_pct'] ?? 0);
        $discAmt = $price * ($discPct / 100);
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
        $units = ceil($fixed / $contrib);
        return [
            'break_even_units'    => $units,
            'break_even_revenue'  => round($units * $price, 2),
            'contribution_margin' => round($contrib, 2),
            'contribution_ratio'  => round(($contrib / $price) * 100, 2),
        ];
    }

    private function calcInventoryValue(array $d): array
    {
        $qty  = (float)($d['quantity'] ?? 0);
        $cost = (float)($d['cost_price'] ?? 0);
        $sell = (float)($d['selling_price'] ?? 0);
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
        $factor    = 1 + $growthPct / 100;
        return [
            'weekly'    => round($avgDaily * 7   * $factor, 2),
            'monthly'   => round($avgDaily * 30  * $factor, 2),
            'quarterly' => round($avgDaily * 90  * $factor, 2),
            'annual'    => round($avgDaily * 365 * $factor, 2),
        ];
    }
}

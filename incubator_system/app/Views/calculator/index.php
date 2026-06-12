<?php /* calculator/index.php */ ?>
<div class="mb-4">
    <h1 class="page-title">Business Calculator</h1>
    <p class="page-subtitle mb-0">Profit, markup, discount, break-even, forecast calculations</p>
</div>

<div class="row g-3">
    <!-- Calculator tabs -->
    <div class="col-12 col-lg-8">
        <div class="card">
            <div class="card-header p-0">
                <ul class="nav nav-tabs border-0 px-3 pt-2" id="calcTabs" role="tablist">
                    <?php
                    $tabs = [
                        ['profit',    'Profit Margin',    'graph-up'],
                        ['markup',    'Markup',           'tag'],
                        ['discount',  'Discount',         'percent'],
                        ['breakeven', 'Break-Even',       'slash-circle'],
                        ['inventory', 'Inventory Value',  'boxes'],
                        ['forecast',  'Revenue Forecast', 'calendar3'],
                    ];
                    foreach ($tabs as $i => [$id,$label,$icon]):
                    ?>
                    <li class="nav-item">
                        <button class="nav-link <?= $i===0?'active':'' ?> small py-2"
                                data-bs-toggle="tab" data-bs-target="#tab-<?= $id ?>">
                            <i class="bi bi-<?= $icon ?> me-1"></i><?= $label ?>
                        </button>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">

                    <!-- Profit Margin -->
                    <div class="tab-pane fade show active" id="tab-profit">
                        <h6 class="fw-bold mb-3">Profit Margin Calculator</h6>
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label class="form-label">Revenue / Selling Price ($)</label>
                                <input type="number" id="pm-revenue" class="form-control" min="0" step="0.01" placeholder="e.g. 150.00">
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label">Cost ($)</label>
                                <input type="number" id="pm-cost" class="form-control" min="0" step="0.01" placeholder="e.g. 95.00">
                            </div>
                            <div class="col-12">
                                <button class="btn btn-primary" onclick="calc('profit_margin')">Calculate</button>
                            </div>
                            <div class="col-12" id="res-profit" style="display:none">
                                <div class="row g-2 mt-1" id="res-profit-content"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Markup -->
                    <div class="tab-pane fade" id="tab-markup">
                        <h6 class="fw-bold mb-3">Markup Calculator</h6>
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label class="form-label">Cost Price ($)</label>
                                <input type="number" id="mk-cost" class="form-control" min="0" step="0.01" placeholder="e.g. 80.00">
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label">Markup Percentage (%)</label>
                                <input type="number" id="mk-pct" class="form-control" min="0" step="0.1" placeholder="e.g. 50">
                            </div>
                            <div class="col-12">
                                <button class="btn btn-primary" onclick="calc('markup')">Calculate</button>
                            </div>
                            <div class="col-12" id="res-markup" style="display:none">
                                <div class="row g-2 mt-1" id="res-markup-content"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Discount -->
                    <div class="tab-pane fade" id="tab-discount">
                        <h6 class="fw-bold mb-3">Discount Calculator</h6>
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label class="form-label">Original Price ($)</label>
                                <input type="number" id="dc-price" class="form-control" min="0" step="0.01" placeholder="e.g. 200.00">
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label">Discount (%)</label>
                                <input type="number" id="dc-pct" class="form-control" min="0" max="100" step="0.1" placeholder="e.g. 15">
                            </div>
                            <div class="col-12">
                                <button class="btn btn-primary" onclick="calc('discount')">Calculate</button>
                            </div>
                            <div class="col-12" id="res-discount" style="display:none">
                                <div class="row g-2 mt-1" id="res-discount-content"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Break-Even -->
                    <div class="tab-pane fade" id="tab-breakeven">
                        <h6 class="fw-bold mb-3">Break-Even Calculator</h6>
                        <div class="row g-3">
                            <div class="col-sm-4">
                                <label class="form-label">Fixed Costs ($)</label>
                                <input type="number" id="be-fixed" class="form-control" min="0" step="0.01" placeholder="e.g. 5000">
                            </div>
                            <div class="col-sm-4">
                                <label class="form-label">Selling Price ($)</label>
                                <input type="number" id="be-price" class="form-control" min="0" step="0.01" placeholder="e.g. 150">
                            </div>
                            <div class="col-sm-4">
                                <label class="form-label">Variable Cost / Unit ($)</label>
                                <input type="number" id="be-var" class="form-control" min="0" step="0.01" placeholder="e.g. 90">
                            </div>
                            <div class="col-12">
                                <button class="btn btn-primary" onclick="calc('break_even')">Calculate</button>
                            </div>
                            <div class="col-12" id="res-breakeven" style="display:none">
                                <div class="row g-2 mt-1" id="res-breakeven-content"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Inventory Value -->
                    <div class="tab-pane fade" id="tab-inventory">
                        <h6 class="fw-bold mb-3">Inventory Valuation</h6>
                        <div class="row g-3">
                            <div class="col-sm-4">
                                <label class="form-label">Quantity</label>
                                <input type="number" id="iv-qty" class="form-control" min="0" placeholder="e.g. 50">
                            </div>
                            <div class="col-sm-4">
                                <label class="form-label">Cost Price ($)</label>
                                <input type="number" id="iv-cost" class="form-control" min="0" step="0.01" placeholder="e.g. 80">
                            </div>
                            <div class="col-sm-4">
                                <label class="form-label">Selling Price ($)</label>
                                <input type="number" id="iv-sell" class="form-control" min="0" step="0.01" placeholder="e.g. 120">
                            </div>
                            <div class="col-12">
                                <button class="btn btn-primary" onclick="calc('inventory_value')">Calculate</button>
                            </div>
                            <div class="col-12" id="res-inventory" style="display:none">
                                <div class="row g-2 mt-1" id="res-inventory-content"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Forecast -->
                    <div class="tab-pane fade" id="tab-forecast">
                        <h6 class="fw-bold mb-3">Revenue Forecast</h6>
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label class="form-label">Average Daily Revenue ($)</label>
                                <input type="number" id="fc-daily" class="form-control" min="0" step="0.01" placeholder="e.g. 500">
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label">Expected Growth (%)</label>
                                <input type="number" id="fc-growth" class="form-control" step="0.1" placeholder="e.g. 10 (or -5 for decline)">
                            </div>
                            <div class="col-12">
                                <button class="btn btn-primary" onclick="calc('revenue_forecast')">Calculate</button>
                            </div>
                            <div class="col-12" id="res-forecast" style="display:none">
                                <div class="row g-2 mt-1" id="res-forecast-content"></div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Quick reference -->
    <div class="col-12 col-lg-4">
        <div class="card">
            <div class="card-header"><i class="bi bi-lightbulb me-2 text-warning"></i>Formulas Reference</div>
            <div class="card-body small">
                <div class="mb-3">
                    <div class="fw-semibold text-primary">Profit Margin</div>
                    <code class="small">(Revenue − Cost) ÷ Revenue × 100</code>
                </div>
                <div class="mb-3">
                    <div class="fw-semibold text-primary">Markup</div>
                    <code class="small">Cost × (1 + Markup%) = Selling Price</code>
                </div>
                <div class="mb-3">
                    <div class="fw-semibold text-primary">Break-Even Units</div>
                    <code class="small">Fixed Costs ÷ (Price − Variable Cost)</code>
                </div>
                <div class="mb-3">
                    <div class="fw-semibold text-primary">Contribution Margin</div>
                    <code class="small">Price − Variable Cost per Unit</code>
                </div>
                <div class="mb-0">
                    <div class="fw-semibold text-primary">Gross Profit Margin</div>
                    <code class="small">Gross Profit ÷ Revenue × 100</code>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const calcInputs = {
    profit_margin:   () => ({ revenue: document.getElementById('pm-revenue').value, cost: document.getElementById('pm-cost').value }),
    markup:          () => ({ cost: document.getElementById('mk-cost').value, markup_pct: document.getElementById('mk-pct').value }),
    discount:        () => ({ price: document.getElementById('dc-price').value, discount_pct: document.getElementById('dc-pct').value }),
    break_even:      () => ({ fixed_cost: document.getElementById('be-fixed').value, price: document.getElementById('be-price').value, variable_cost: document.getElementById('be-var').value }),
    inventory_value: () => ({ quantity: document.getElementById('iv-qty').value, cost_price: document.getElementById('iv-cost').value, selling_price: document.getElementById('iv-sell').value }),
    revenue_forecast:() => ({ avg_daily_revenue: document.getElementById('fc-daily').value, growth_pct: document.getElementById('fc-growth').value }),
};

const resultLabels = {
    profit_margin:   [['Gross Profit','gross_profit'],['Gross Margin','gross_margin','%'],['Markup %','markup_pct','%']],
    markup:          [['Selling Price','selling_price'],['Profit Amount','profit_amount'],['Profit Margin','profit_margin','%']],
    discount:        [['Discount Amount','discount_amount'],['Final Price','final_price'],['Savings','savings']],
    break_even:      [['Units Needed','break_even_units',''],['Revenue at Break-Even','break_even_revenue'],['Contribution Margin','contribution_margin'],['Contribution Ratio','contribution_ratio','%']],
    inventory_value: [['Cost Value','total_cost_value'],['Retail Value','total_retail_value'],['Potential Profit','potential_profit'],['Margin %','margin_pct','%']],
    revenue_forecast:[['Weekly','weekly'],['Monthly','monthly'],['Quarterly','quarterly'],['Annual','annual']],
};

async function calc(type) {
    const inputs = calcInputs[type]();
    try {
        const res = await App.fetch(`${APP_URL}/calculator/calculate`, {
            method: 'POST',
            body: { type, ...inputs, _csrf_token: CSRF_TOKEN }
        });
        const data    = res.data;
        const labels  = resultLabels[type] || [];
        const tabId   = type.replace('_','-').replace('_','');
        const resId   = 'res-' + type.split('_')[0];
        const contentId = resId + '-content';

        const el = document.getElementById(contentId) || document.getElementById('res-'+type.replace('_','-')+'-content');
        const wrapper = el?.closest('[id^="res-"]') || document.getElementById(resId);
        if (!el || !wrapper) return;

        el.innerHTML = labels.map(([label, key, suffix]) => {
            const val = data[key];
            const display = suffix === '%' ? val + '%' : '$' + parseFloat(val||0).toFixed(2);
            const isGood  = parseFloat(val||0) >= 0;
            return `<div class="col-6 col-sm-3">
                <div class="p-3 bg-light rounded text-center">
                    <div class="fw-bold fs-5 ${key.includes('profit')||key.includes('savings')?isGood?'text-success':'text-danger':''}">${display}</div>
                    <div class="text-muted small">${label}</div>
                </div>
            </div>`;
        }).join('');
        wrapper.style.display = '';
    } catch(e) { App.toast(e.message,'danger'); }
}
</script>

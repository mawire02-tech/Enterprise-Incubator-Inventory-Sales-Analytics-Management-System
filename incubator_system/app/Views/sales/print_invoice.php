<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Invoice <?= htmlspecialchars($sale['invoice_number']) ?></title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:Arial,sans-serif;font-size:12px;color:#111;background:#fff;padding:20px}
.inv-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:24px;padding-bottom:16px;border-bottom:2px solid #2563eb}
.company-name{font-size:18px;font-weight:700;color:#2563eb}
.inv-title{font-size:22px;font-weight:700;text-align:right;color:#2563eb}
.inv-meta{text-align:right;margin-top:4px;font-size:11px;color:#555}
.section-title{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#888;margin-bottom:6px;margin-top:16px}
table{width:100%;border-collapse:collapse;margin-top:8px}
thead th{background:#f8fafc;border-bottom:2px solid #e2e8f0;padding:6px 8px;font-size:11px;text-align:left;font-weight:700;text-transform:uppercase;letter-spacing:.4px;color:#555}
tbody td{padding:6px 8px;border-bottom:1px solid #f1f5f9;font-size:12px;vertical-align:top}
tfoot td{padding:5px 8px;font-size:12px}
.text-right{text-align:right}
.text-center{text-align:center}
.total-row{background:#2563eb;color:#fff;font-weight:700}
.total-row td{padding:8px}
.info-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px}
.info-box{background:#f8fafc;border-radius:6px;padding:10px}
.info-box .label{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#888;margin-bottom:4px}
.info-box .value{font-size:12px}
.badge-paid{background:#dcfce7;color:#15803d;padding:2px 8px;border-radius:9px;font-size:10px;font-weight:700}
.badge-partial{background:#fef3c7;color:#d97706;padding:2px 8px;border-radius:9px;font-size:10px;font-weight:700}
.badge-unpaid{background:#fee2e2;color:#dc2626;padding:2px 8px;border-radius:9px;font-size:10px;font-weight:700}
.footer{margin-top:30px;padding-top:12px;border-top:1px solid #e2e8f0;text-align:center;font-size:10px;color:#888}
@media print{
    body{padding:0}
    .no-print{display:none}
    @page{margin:15mm}
}
</style>
</head>
<body>

<div class="no-print" style="margin-bottom:16px;text-align:right">
    <button onclick="window.print()" style="background:#2563eb;color:#fff;border:none;padding:8px 20px;border-radius:6px;cursor:pointer;font-size:13px">
        🖨 Print Invoice
    </button>
    <button onclick="window.close()" style="background:#e2e8f0;color:#333;border:none;padding:8px 20px;border-radius:6px;cursor:pointer;font-size:13px;margin-left:8px">
        Close
    </button>
</div>

<!-- Header -->
<div class="inv-header">
    <div>
        <div class="company-name"><?= htmlspecialchars($company['company_name'] ?? APP_NAME) ?></div>
        <div style="color:#555;margin-top:4px;font-size:11px">
            <?= htmlspecialchars($company['company_address'] ?? '') ?><br>
            <?= htmlspecialchars($company['company_phone'] ?? '') ?>
            <?php if ($company['company_email'] ?? ''): ?>
             &nbsp;|&nbsp; <?= htmlspecialchars($company['company_email']) ?>
            <?php endif; ?>
        </div>
    </div>
    <div>
        <div class="inv-title">INVOICE</div>
        <div class="inv-meta">
            <strong><?= htmlspecialchars($sale['invoice_number']) ?></strong><br>
            Date: <?= date('d/m/Y', strtotime($sale['sale_date'])) ?><br>
            <span class="badge-<?= $sale['payment_status'] ?>"><?= strtoupper($sale['payment_status']) ?></span>
        </div>
    </div>
</div>

<!-- Customer & Payment Info -->
<div class="info-grid">
    <div class="info-box">
        <div class="label">Bill To</div>
        <div class="value">
            <strong><?= htmlspecialchars($sale['customer_full_name'] ?? $sale['customer_name'] ?? 'Walk-in Customer') ?></strong><br>
            <?php if ($sale['customer_phone_full'] ?? $sale['customer_phone']): ?>
            <?= htmlspecialchars($sale['customer_phone_full'] ?? $sale['customer_phone']) ?>
            <?php endif; ?>
        </div>
    </div>
    <div class="info-box">
        <div class="label">Payment Details</div>
        <div class="value">
            Method: <strong><?= ucfirst(str_replace('_',' ',$sale['payment_method'])) ?></strong><br>
            Paid: <strong>$<?= number_format((float)$sale['amount_paid'],2) ?></strong><br>
            <?php if ((float)$sale['amount_due'] > 0): ?>
            Balance Due: <strong style="color:#dc2626">$<?= number_format((float)$sale['amount_due'],2) ?></strong>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Items -->
<table>
    <thead><tr>
        <th>#</th>
        <th>Description</th>
        <th class="text-center">Qty</th>
        <th class="text-right">Unit Price</th>
        <?php if ((float)$sale['discount_amount'] > 0): ?><th class="text-right">Discount</th><?php endif; ?>
        <th class="text-right">Amount</th>
    </tr></thead>
    <tbody>
    <?php foreach ($sale['items'] as $i => $item): ?>
    <tr>
        <td><?= $i+1 ?></td>
        <td>
            <strong><?= htmlspecialchars($item['product_name']) ?></strong>
            <span style="color:#888;font-size:10px"> (<?= htmlspecialchars($item['sku']) ?>)</span>
        </td>
        <td class="text-center"><?= $item['quantity'] ?></td>
        <td class="text-right">$<?= number_format((float)$item['unit_price'],2) ?></td>
        <?php if ((float)$sale['discount_amount'] > 0): ?>
        <td class="text-right">
            <?= (float)$item['discount_amount']>0 ? '$'.number_format((float)$item['discount_amount'],2) : '—' ?>
        </td>
        <?php endif; ?>
        <td class="text-right"><strong>$<?= number_format((float)$item['line_total'],2) ?></strong></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr><td colspan="<?= 4 + ((float)$sale['discount_amount']>0?1:0) ?>" class="text-right" style="color:#555">Subtotal</td>
            <td class="text-right">$<?= number_format((float)$sale['subtotal'],2) ?></td></tr>
        <?php if ((float)$sale['discount_amount'] > 0): ?>
        <tr><td colspan="<?= 4 + ((float)$sale['discount_amount']>0?1:0) ?>" class="text-right" style="color:#555">Discount</td>
            <td class="text-right" style="color:#dc2626">−$<?= number_format((float)$sale['discount_amount'],2) ?></td></tr>
        <?php endif; ?>
        <?php if ((float)$sale['tax_amount'] > 0): ?>
        <tr><td colspan="<?= 4 + ((float)$sale['discount_amount']>0?1:0) ?>" class="text-right" style="color:#555">Tax (<?= $sale['tax_rate'] ?>%)</td>
            <td class="text-right">$<?= number_format((float)$sale['tax_amount'],2) ?></td></tr>
        <?php endif; ?>
        <tr class="total-row">
            <td colspan="<?= 4 + ((float)$sale['discount_amount']>0?1:0) ?>" class="text-right">TOTAL</td>
            <td class="text-right" style="font-size:14px">$<?= number_format((float)$sale['total_amount'],2) ?></td>
        </tr>
    </tfoot>
</table>

<?php if ($sale['notes']): ?>
<div class="section-title" style="margin-top:16px">Notes</div>
<p style="font-size:11px;color:#555;margin-top:4px"><?= htmlspecialchars($sale['notes']) ?></p>
<?php endif; ?>

<div class="footer">
    <?= htmlspecialchars($company['company_name'] ?? APP_NAME) ?> &nbsp;|&nbsp;
    <?= htmlspecialchars($company['company_address'] ?? '') ?> &nbsp;|&nbsp;
    <?= htmlspecialchars($company['company_phone'] ?? '') ?><br>
    Thank you for your business!
</div>

</body>
</html>

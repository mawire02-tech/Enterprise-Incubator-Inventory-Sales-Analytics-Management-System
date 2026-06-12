<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\Auth;
use App\Helpers\Session;
use App\Helpers\Validator;
use App\Services\InventoryService;
use App\Services\AuditService;

class InventoryController extends Controller
{
    private InventoryService $inventory;
    private AuditService $audit;

    public function __construct()
    {
        parent::__construct();
        $this->inventory = new InventoryService();
        $this->audit     = new AuditService();
    }

    // ═══════════════════════════════════════════════
    // PRODUCTS
    // ═══════════════════════════════════════════════

    public function products(): void
    {
        $this->requirePermission('products.view');

        $search  = $this->get('q', '');
        $where   = "p.is_active = 1";
        $params  = [];

        if ($search) {
            $where  .= " AND (p.name LIKE ? OR p.sku LIKE ? OR p.brand LIKE ?)";
            $params  = array_fill(0, 3, "%{$search}%");
        }

        $total    = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM products p WHERE {$where}", $params);
        $paginate = $this->paginate($total);

        $products = $this->db->fetchAll(
            "SELECT p.*,
                    COALESCE(SUM(ib.quantity_current),0) AS stock_qty,
                    COALESCE(SUM(ib.quantity_current * ib.cost_price),0) AS stock_value
             FROM products p
             LEFT JOIN inventory_batches ib ON ib.product_id = p.id AND ib.status = 'active'
             WHERE {$where}
             GROUP BY p.id
             ORDER BY p.name ASC
             LIMIT ? OFFSET ?",
            [...$params, $paginate['per_page'], $paginate['offset']]
        );

        $this->view('inventory.products', [
            'pageTitle' => 'Products',
            'products'  => $products,
            'search'    => $search,
            'paginate'  => $paginate,
        ]);
    }

    public function createProduct(): void
    {
        $this->requirePermission('products.create');
        $this->view('inventory.product_form', ['pageTitle' => 'Add Product', 'product' => null]);
    }

    public function storeProduct(): void
    {
        $this->requirePermission('products.create');
        $this->validateCsrf();

        $data = [
            'sku'                 => strtoupper(trim($this->post('sku', ''))),
            'name'                => trim($this->post('name', '')),
            'description'         => trim($this->post('description', '')),
            'category'            => trim($this->post('category', '')),
            'capacity'            => $this->post('capacity'),
            'brand'               => trim($this->post('brand', '')),
            'model'               => trim($this->post('model', '')),
            'unit'                => $this->post('unit', 'unit'),
            'low_stock_threshold' => (int)$this->post('low_stock_threshold', 5),
            'created_by'          => Auth::id(),
        ];

        $v = Validator::make($data, [
            'sku'  => 'required|min:2|max:50',
            'name' => 'required|min:2|max:200',
        ]);

        if ($v->fails()) {
            Session::flash('error', implode(' ', array_merge(...array_values($v->errors()))));
            $this->redirectRoute('/inventory/products/create');
            return;
        }

        if ($this->db->fetchColumn("SELECT COUNT(*) FROM products WHERE sku = ?", [$data['sku']])) {
            Session::flash('error', "SKU '{$data['sku']}' already exists.");
            $this->redirectRoute('/inventory/products/create');
            return;
        }

        $id = $this->db->execute(
            "INSERT INTO products (sku,name,description,category,capacity,brand,model,unit,low_stock_threshold,created_by)
             VALUES (?,?,?,?,?,?,?,?,?,?)",
            array_values($data)
        );
        $id = (int)$this->db->lastInsertId();

        $this->audit->log('product_created', 'inventory', "Product '{$data['name']}' (SKU: {$data['sku']}) created.",
            [], $data, 'product', $id);

        Session::flash('success', "Product '{$data['name']}' created successfully.");
        $this->redirectRoute('/inventory/products');
    }

    public function editProduct(array $params): void
    {
        $this->requirePermission('products.edit');
        $product = $this->db->fetchOne("SELECT * FROM products WHERE id = ? AND is_active = 1", [$params['id']]);
        if (!$product) { $this->redirectRoute('/inventory/products'); return; }

        $this->view('inventory.product_form', ['pageTitle' => 'Edit Product', 'product' => $product]);
    }

    public function updateProduct(array $params): void
    {
        $this->requirePermission('products.edit');
        $this->validateCsrf();

        $id   = (int)$params['id'];
        $old  = $this->db->fetchOne("SELECT * FROM products WHERE id = ?", [$id]);
        if (!$old) { $this->redirectRoute('/inventory/products'); return; }

        $data = [
            'name'                => trim($this->post('name', '')),
            'description'         => trim($this->post('description', '')),
            'category'            => trim($this->post('category', '')),
            'capacity'            => $this->post('capacity'),
            'brand'               => trim($this->post('brand', '')),
            'model'               => trim($this->post('model', '')),
            'unit'                => $this->post('unit', 'unit'),
            'low_stock_threshold' => (int)$this->post('low_stock_threshold', 5),
            'is_active'           => (int)(bool)$this->post('is_active', 1),
        ];

        $this->db->execute(
            "UPDATE products SET name=?,description=?,category=?,capacity=?,brand=?,model=?,unit=?,low_stock_threshold=?,is_active=? WHERE id=?",
            [...array_values($data), $id]
        );

        $this->audit->log('product_updated', 'inventory', "Product '{$old['name']}' updated.",
            $old, $data, 'product', $id);

        Session::flash('success', 'Product updated.');
        $this->redirectRoute('/inventory/products');
    }

    // ═══════════════════════════════════════════════
    // BATCHES / STOCK
    // ═══════════════════════════════════════════════

    public function batches(): void
    {
        $this->requirePermission('inventory.view');

        $status  = $this->get('status', 'active');
        $product = (int)$this->get('product_id', 0);
        $where   = ['1=1'];
        $params  = [];

        if ($status !== 'all') { $where[] = 'ib.status = ?'; $params[] = $status; }
        if ($product)          { $where[] = 'ib.product_id = ?'; $params[] = $product; }

        $whereSql = implode(' AND ', $where);
        $total    = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM inventory_batches ib WHERE {$whereSql}", $params);
        $paginate = $this->paginate($total);

        $batches = $this->db->fetchAll(
            "SELECT ib.*, p.name AS product_name, p.sku, u.full_name AS created_by_name
             FROM inventory_batches ib
             JOIN products p ON p.id = ib.product_id
             LEFT JOIN users u ON u.id = ib.created_by
             WHERE {$whereSql}
             ORDER BY ib.acquisition_date DESC
             LIMIT ? OFFSET ?",
            [...$params, $paginate['per_page'], $paginate['offset']]
        );

        $products = $this->db->fetchAll("SELECT id, name, sku FROM products WHERE is_active=1 ORDER BY name");

        $this->view('inventory.batches', [
            'pageTitle' => 'Inventory Batches',
            'batches'   => $batches,
            'products'  => $products,
            'status'    => $status,
            'paginate'  => $paginate,
        ]);
    }

    public function receiveBatch(): void
    {
        $this->requirePermission('inventory.receive');
        $products = $this->db->fetchAll("SELECT id, name, sku FROM products WHERE is_active=1 ORDER BY name");
        $this->view('inventory.receive_batch', ['pageTitle' => 'Receive Stock', 'products' => $products]);
    }

    public function storeReceiveBatch(): void
    {
        $this->requirePermission('inventory.receive');
        $this->validateCsrf();

        $data = [
            'product_id'      => (int)$this->post('product_id'),
            'quantity'        => (int)$this->post('quantity'),
            'cost_price'      => (float)$this->post('cost_price'),
            'selling_price'   => (float)$this->post('selling_price'),
            'acquisition_date'=> $this->post('acquisition_date', date('Y-m-d')),
            'supplier_name'   => trim($this->post('supplier_name', '')),
            'supplier_ref'    => trim($this->post('supplier_ref', '')),
            'expiry_date'     => $this->post('expiry_date') ?: null,
            'notes'           => trim($this->post('notes', '')),
        ];

        $v = Validator::make($data, [
            'product_id'       => 'required|integer|min_val:1',
            'quantity'         => 'required|integer|min_val:1',
            'cost_price'       => 'required|numeric|min_val:0.01',
            'selling_price'    => 'required|numeric|min_val:0.01',
            'acquisition_date' => 'required|date',
        ]);

        if ($v->fails()) {
            Session::flash('error', implode(' ', array_merge(...array_values($v->errors()))));
            $this->redirectRoute('/inventory/batches/receive');
            return;
        }

        try {
            $result = $this->inventory->receiveBatch($data);
            Session::flash('success', "Stock received successfully. Batch code: {$result['batch_code']}");
            $this->redirectRoute('/inventory/batches');
        } catch (\Throwable $e) {
            Session::flash('error', $e->getMessage());
            $this->redirectRoute('/inventory/batches/receive');
        }
    }

    public function batchDetail(array $params): void
    {
        $this->requirePermission('inventory.view');

        $batch = $this->db->fetchOne(
            "SELECT ib.*, p.name AS product_name, p.sku, p.low_stock_threshold
             FROM inventory_batches ib JOIN products p ON p.id = ib.product_id
             WHERE ib.id = ?",
            [$params['id']]
        );
        if (!$batch) { $this->redirectRoute('/inventory/batches'); return; }

        $movements = $this->db->fetchAll(
            "SELECT im.*, u.full_name AS by_name FROM inventory_movements im
             LEFT JOIN users u ON u.id = im.performed_by
             WHERE im.batch_id = ? ORDER BY im.performed_at DESC LIMIT 100",
            [$params['id']]
        );

        $this->view('inventory.batch_detail', [
            'pageTitle' => 'Batch Detail: ' . $batch['batch_code'],
            'batch'     => $batch,
            'movements' => $movements,
        ]);
    }

    public function adjustStock(array $params): void
    {
        $this->requirePermission('inventory.adjust');

        $batch = $this->db->fetchOne(
            "SELECT ib.*, p.name AS product_name, p.sku FROM inventory_batches ib
             JOIN products p ON p.id = ib.product_id WHERE ib.id = ?",
            [$params['id']]
        );
        if (!$batch) { $this->redirectRoute('/inventory/batches'); return; }

        $this->view('inventory.adjust', ['pageTitle' => 'Adjust Stock', 'batch' => $batch]);
    }

    public function storeAdjustment(array $params): void
    {
        $this->requirePermission('inventory.adjust');
        $this->validateCsrf();

        $data = [
            'batch_id'        => (int)$params['id'],
            'adjustment_type' => $this->post('adjustment_type', 'add'),
            'quantity'        => (int)$this->post('quantity'),
            'reason'          => trim($this->post('reason', '')),
            'notes'           => trim($this->post('notes', '')),
        ];

        $v = Validator::make($data, [
            'adjustment_type' => 'required|in:add,remove,damage,correction',
            'quantity'        => 'required|integer|min_val:1',
            'reason'          => 'required|min:5',
        ]);

        if ($v->fails()) {
            Session::flash('error', implode(' ', array_merge(...array_values($v->errors()))));
            $this->redirectRoute("/inventory/batches/{$params['id']}/adjust");
            return;
        }

        try {
            $this->inventory->adjustStock($data);
            Session::flash('success', 'Stock adjustment recorded successfully.');
            $this->redirectRoute("/inventory/batches/{$params['id']}");
        } catch (\Throwable $e) {
            Session::flash('error', $e->getMessage());
            $this->redirectRoute("/inventory/batches/{$params['id']}/adjust");
        }
    }

    public function archiveBatch(array $params): void
    {
        $this->requirePermission('inventory.edit');
        $this->validateCsrf();

        try {
            $result = $this->inventory->archiveBatch((int)$params['id']);
            Session::flash('success', 'Batch archived. Net Profit: $' . number_format($result['net_profit'], 2));
        } catch (\Throwable $e) {
            Session::flash('error', $e->getMessage());
        }
        $this->redirectRoute('/inventory/batches');
    }

    // ─── AJAX: Stock levels ──────────────────────────────────
    public function stockLevels(): void
    {
        $this->requirePermission('inventory.view');
        $this->success($this->inventory->getStockLevels());
    }

    // ─── AJAX: Movements ────────────────────────────────────
    public function movements(): void
    {
        $this->requirePermission('inventory.view');
        $filters = $this->all();
        $page    = (int)$this->get('page', 1);
        $result  = $this->inventory->getMovements($filters, $page);
        $this->success($result);
    }
}
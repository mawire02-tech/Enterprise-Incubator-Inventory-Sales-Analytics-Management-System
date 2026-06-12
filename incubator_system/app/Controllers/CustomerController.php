<?php
// ─── CustomerController ──────────────────────────────────────────────────────
namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\Auth;
use App\Helpers\Session;
use App\Helpers\Validator;
use App\Services\AuditService;

class CustomerController extends Controller
{
    private AuditService $audit;

    public function __construct()
    {
        parent::__construct();
        $this->audit = new AuditService();
    }

    public function index(): void
    {
        $this->requirePermission('customers.view');

        $search  = $this->get('q', '');
        $where   = 'deleted_at IS NULL';
        $params  = [];

        if ($search) {
            $where   .= ' AND (full_name LIKE ? OR phone LIKE ? OR email LIKE ? OR customer_code LIKE ?)';
            $params   = array_fill(0, 4, "%{$search}%");
        }

        $total    = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM customers WHERE {$where}", $params);
        $paginate = $this->paginate($total);

        $customers = $this->db->fetchAll(
            "SELECT c.*,
                    COUNT(s.id)          AS total_orders,
                    COALESCE(SUM(s.total_amount),0) AS lifetime_value
             FROM customers c
             LEFT JOIN sales s ON s.customer_id = c.id AND s.status = 'completed'
             WHERE {$where}
             GROUP BY c.id
             ORDER BY c.full_name ASC
             LIMIT ? OFFSET ?",
            [...$params, $paginate['per_page'], $paginate['offset']]
        );

        $this->view('customers.index', [
            'pageTitle' => 'Customers',
            'customers' => $customers,
            'search'    => $search,
            'paginate'  => $paginate,
        ]);
    }

    public function show(array $params): void
    {
        $this->requirePermission('customers.view');

        $customer = $this->db->fetchOne("SELECT * FROM customers WHERE id = ? AND deleted_at IS NULL", [$params['id']]);
        if (!$customer) { Session::flash('error', 'Customer not found.'); $this->redirectRoute('/customers'); return; }

        $purchases = $this->db->fetchAll(
            "SELECT s.*, COUNT(si.id) AS item_count FROM sales s
             LEFT JOIN sale_items si ON si.sale_id = s.id
             WHERE s.customer_id = ? ORDER BY s.sale_date DESC LIMIT 50",
            [$customer['id']]
        );

        $stats = $this->db->fetchOne(
            "SELECT COUNT(*) AS total_orders, COALESCE(SUM(total_amount),0) AS lifetime_value,
                    COALESCE(MAX(total_amount),0) AS largest_order, COALESCE(AVG(total_amount),0) AS avg_order
             FROM sales WHERE customer_id = ? AND status='completed'",
            [$customer['id']]
        );

        $this->view('customers.show', [
            'pageTitle' => $customer['full_name'],
            'customer'  => $customer,
            'purchases' => $purchases,
            'stats'     => $stats,
        ]);
    }

    public function create(): void
    {
        $this->requirePermission('customers.create');
        $this->view('customers.form', ['pageTitle' => 'Add Customer', 'customer' => null]);
    }

    public function store(): void
    {
        $this->requirePermission('customers.create');
        $this->validateCsrf();

        $data = [
            'full_name' => trim($this->post('full_name', '')),
            'email'     => trim($this->post('email', '')),
            'phone'     => trim($this->post('phone', '')),
            'address'   => trim($this->post('address', '')),
            'city'      => trim($this->post('city', '')),
            'notes'     => trim($this->post('notes', '')),
            'created_by'=> Auth::id(),
        ];

        $v = Validator::make($data, ['full_name' => 'required|min:2|max:150']);
        if ($v->fails()) {
            Session::flash('error', $v->firstError('full_name'));
            $this->redirectRoute('/customers/create');
            return;
        }

        $code = $this->generateCustomerCode();
        $this->db->execute(
            "INSERT INTO customers (customer_code,full_name,email,phone,address,city,notes,created_by) VALUES (?,?,?,?,?,?,?,?)",
            [$code, $data['full_name'], $data['email'], $data['phone'], $data['address'], $data['city'], $data['notes'], $data['created_by']]
        );
        $id = (int)$this->db->lastInsertId();

        $this->audit->log('customer_created', 'customers', "Customer '{$data['full_name']}' created.", [], $data, 'customer', $id);
        Session::flash('success', "Customer '{$data['full_name']}' added.");
        $this->redirectRoute('/customers');
    }

    public function edit(array $params): void
    {
        $this->requirePermission('customers.edit');
        $customer = $this->db->fetchOne("SELECT * FROM customers WHERE id=? AND deleted_at IS NULL", [$params['id']]);
        if (!$customer) { $this->redirectRoute('/customers'); return; }
        $this->view('customers.form', ['pageTitle' => 'Edit Customer', 'customer' => $customer]);
    }

    public function update(array $params): void
    {
        $this->requirePermission('customers.edit');
        $this->validateCsrf();

        $id  = (int)$params['id'];
        $old = $this->db->fetchOne("SELECT * FROM customers WHERE id=?", [$id]);

        $data = [
            'full_name' => trim($this->post('full_name', '')),
            'email'     => trim($this->post('email', '')),
            'phone'     => trim($this->post('phone', '')),
            'address'   => trim($this->post('address', '')),
            'city'      => trim($this->post('city', '')),
            'notes'     => trim($this->post('notes', '')),
            'is_active' => (int)(bool)$this->post('is_active', 1),
        ];

        $this->db->execute(
            "UPDATE customers SET full_name=?,email=?,phone=?,address=?,city=?,notes=?,is_active=? WHERE id=?",
            [...array_values($data), $id]
        );

        $this->audit->log('customer_updated', 'customers', "Customer '{$old['full_name']}' updated.", $old, $data, 'customer', $id);
        Session::flash('success', 'Customer updated.');
        $this->redirectRoute('/customers');
    }

    public function destroy(array $params): void
    {
        $this->requirePermission('customers.delete');
        $this->validateCsrf();

        $customer = $this->db->fetchOne("SELECT * FROM customers WHERE id=?", [$params['id']]);
        $this->db->execute("UPDATE customers SET deleted_at=NOW() WHERE id=?", [$params['id']]);
        $this->audit->log('customer_deleted', 'customers', "Customer '{$customer['full_name']}' deleted.",
            $customer, [], 'customer', (int)$params['id'], 'medium');

        if ($this->isAjax()) { $this->success(null, 'Customer deleted.'); return; }
        Session::flash('success', 'Customer deleted.');
        $this->redirectRoute('/customers');
    }

    // AJAX search for POS
    public function search(): void
    {
        $q = $this->get('q', '');
        if (strlen($q) < 2) { $this->success([]); return; }
        $results = $this->db->fetchAll(
            "SELECT id, customer_code, full_name, phone, email FROM customers
             WHERE deleted_at IS NULL AND (full_name LIKE ? OR phone LIKE ? OR customer_code LIKE ?)
             LIMIT 10",
            ["%{$q}%", "%{$q}%", "%{$q}%"]
        );
        $this->success($results);
    }

    private function generateCustomerCode(): string
    {
        $last = $this->db->fetchColumn("SELECT MAX(CAST(SUBSTRING(customer_code,6) AS UNSIGNED)) FROM customers WHERE customer_code LIKE 'CUST-%'");
        return 'CUST-' . str_pad(((int)$last + 1), 4, '0', STR_PAD_LEFT);
    }
}

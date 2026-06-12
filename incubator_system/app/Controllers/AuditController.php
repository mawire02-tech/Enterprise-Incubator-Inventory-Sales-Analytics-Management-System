<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\AuditService;

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

    public function feed(): void
    {
        $this->requirePermission('audit.view');
        $this->success($this->audit->getRecent(20));
    }
}

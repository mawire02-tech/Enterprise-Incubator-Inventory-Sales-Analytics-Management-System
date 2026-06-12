<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\Auth;
use App\Helpers\Session;
use App\Helpers\Validator;
use App\Services\AuditService;

class UserController extends Controller
{
    private AuditService $audit;

    public function __construct()
    {
        parent::__construct();
        $this->audit = new AuditService();
    }

    public function index(): void
    {
        $this->requirePermission('users.view');

        $search = $this->get('q', '');
        $role   = (int)$this->get('role_id', 0);
        $where  = ['u.deleted_at IS NULL'];
        $params = [];

        if ($search) {
            $where[]  = '(u.full_name LIKE ? OR u.username LIKE ? OR u.email LIKE ?)';
            $params   = array_fill(0, 3, "%{$search}%");
        }
        if ($role) { $where[] = 'u.role_id = ?'; $params[] = $role; }

        $whereSql = implode(' AND ', $where);
        $total    = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM users u WHERE {$whereSql}", $params);
        $paginate = $this->paginate($total);

        $users = $this->db->fetchAll(
            "SELECT u.*, r.display_name AS role_display FROM users u
             JOIN roles r ON r.id = u.role_id
             WHERE {$whereSql}
             ORDER BY u.full_name ASC
             LIMIT ? OFFSET ?",
            [...$params, $paginate['per_page'], $paginate['offset']]
        );

        $roles = $this->db->fetchAll("SELECT * FROM roles ORDER BY id");

        $this->view('users.index', [
            'pageTitle' => 'User Management',
            'users'     => $users,
            'roles'     => $roles,
            'search'    => $search,
            'roleFilter'=> $role,
            'paginate'  => $paginate,
        ]);
    }

    public function create(): void
    {
        $this->requirePermission('users.create');
        $roles = $this->db->fetchAll("SELECT * FROM roles ORDER BY id");
        $this->view('users.form', ['pageTitle' => 'Create User', 'user' => null, 'roles' => $roles]);
    }

    public function store(): void
    {
        $this->requirePermission('users.create');
        $this->validateCsrf();

        $data = [
            'role_id'   => (int)$this->post('role_id'),
            'username'  => strtolower(trim($this->post('username', ''))),
            'email'     => strtolower(trim($this->post('email', ''))),
            'full_name' => trim($this->post('full_name', '')),
            'phone'     => trim($this->post('phone', '')),
            'password'  => $this->post('password', ''),
            'password_confirmation' => $this->post('password_confirmation', ''),
            'must_change_password'  => (int)(bool)$this->post('must_change_password', 0),
        ];

        $v = Validator::make($data, [
            'role_id'   => 'required|integer|min_val:1',
            'username'  => 'required|min:3|max:50',
            'email'     => 'required|email|max:150',
            'full_name' => 'required|min:2|max:150',
            'password'  => 'required|min:8|confirmed',
        ]);

        if ($v->fails()) {
            Session::flash('error', implode(' ', array_merge(...array_values($v->errors()))));
            $this->redirectRoute('/users/create');
            return;
        }

        if ($this->db->fetchColumn("SELECT COUNT(*) FROM users WHERE username=?", [$data['username']])) {
            Session::flash('error', "Username '{$data['username']}' already exists."); $this->redirectRoute('/users/create'); return;
        }
        if ($this->db->fetchColumn("SELECT COUNT(*) FROM users WHERE email=?", [$data['email']])) {
            Session::flash('error', "Email '{$data['email']}' already in use."); $this->redirectRoute('/users/create'); return;
        }

        $hash = password_hash($data['password'], PASSWORD_ARGON2ID, [
            'memory_cost' => ARGON_MEMORY, 'time_cost' => ARGON_TIME, 'threads' => ARGON_THREADS,
        ]);

        $this->db->execute(
            "INSERT INTO users (role_id,username,email,password_hash,full_name,phone,must_change_password,created_by)
             VALUES (?,?,?,?,?,?,?,?)",
            [$data['role_id'], $data['username'], $data['email'], $hash,
             $data['full_name'], $data['phone'], $data['must_change_password'], Auth::id()]
        );
        $id = (int)$this->db->lastInsertId();

        $this->audit->logUserAction('user_created', $id, $data['username']);
        Session::flash('success', "User '{$data['username']}' created.");
        $this->redirectRoute('/users');
    }

    public function edit(array $params): void
    {
        $this->requirePermission('users.edit');
        $user  = $this->db->fetchOne("SELECT * FROM users WHERE id=? AND deleted_at IS NULL", [$params['id']]);
        if (!$user) { $this->redirectRoute('/users'); return; }
        $roles = $this->db->fetchAll("SELECT * FROM roles ORDER BY id");

        // Current permissions for this user (role + overrides)
        $rolePerms = $this->db->fetchAll(
            "SELECT p.*, 1 AS from_role, NULL AS user_granted FROM permissions p
             JOIN role_permissions rp ON rp.permission_id = p.id WHERE rp.role_id = ?",
            [$user['role_id']]
        );
        $userPerms = $this->db->fetchAll(
            "SELECT p.*, 0 AS from_role, up.granted AS user_granted FROM permissions p
             JOIN user_permissions up ON up.permission_id = p.id WHERE up.user_id = ?",
            [$user['id']]
        );

        $this->view('users.form', [
            'pageTitle'  => 'Edit User',
            'user'       => $user,
            'roles'      => $roles,
            'rolePerms'  => $rolePerms,
            'userPerms'  => $userPerms,
            'allPerms'   => $this->db->fetchAll("SELECT * FROM permissions ORDER BY module,action"),
        ]);
    }

    public function update(array $params): void
    {
        $this->requirePermission('users.edit');
        $this->validateCsrf();

        $id  = (int)$params['id'];
        $old = $this->db->fetchOne("SELECT * FROM users WHERE id=?", [$id]);
        if (!$old) { $this->redirectRoute('/users'); return; }

        $data = [
            'full_name' => trim($this->post('full_name', '')),
            'email'     => strtolower(trim($this->post('email', ''))),
            'phone'     => trim($this->post('phone', '')),
            'is_active' => (int)(bool)$this->post('is_active', 1),
        ];

        // Only admin can change roles
        if (Auth::can('users.change_role')) {
            $data['role_id'] = (int)$this->post('role_id', $old['role_id']);
        }

        $this->db->execute(
            "UPDATE users SET full_name=?,email=?,phone=?,is_active=?" . (isset($data['role_id']) ? ',role_id=?' : '') . " WHERE id=?",
            isset($data['role_id'])
                ? [$data['full_name'],$data['email'],$data['phone'],$data['is_active'],$data['role_id'],$id]
                : [$data['full_name'],$data['email'],$data['phone'],$data['is_active'],$id]
        );

        // Handle user-level permission overrides
        if (Auth::can('users.change_role')) {
            $grantedPerms = (array)$this->post('permissions', []);
            $this->db->execute("DELETE FROM user_permissions WHERE user_id=?", [$id]);
            foreach ($grantedPerms as $permId => $granted) {
                $this->db->execute(
                    "INSERT INTO user_permissions (user_id,permission_id,granted,granted_by) VALUES (?,?,?,?)",
                    [$id, (int)$permId, (int)$granted, Auth::id()]
                );
            }
        }

        $this->audit->logUserAction('user_updated', $id, $old['username']);
        Session::flash('success', 'User updated.');
        $this->redirectRoute('/users');
    }

    public function resetPassword(array $params): void
    {
        $this->requirePermission('users.edit');
        $this->validateCsrf();

        $id   = (int)$params['id'];
        $user = $this->db->fetchOne("SELECT * FROM users WHERE id=?", [$id]);
        if (!$user) { $this->error('User not found.'); return; }

        $newPass = $this->post('new_password', '');
        if (strlen($newPass) < 8) { $this->error('Password must be at least 8 characters.'); return; }

        $hash = password_hash($newPass, PASSWORD_ARGON2ID, [
            'memory_cost' => ARGON_MEMORY, 'time_cost' => ARGON_TIME, 'threads' => ARGON_THREADS,
        ]);

        $this->db->execute(
            "UPDATE users SET password_hash=?,must_change_password=1,password_changed_at=NOW() WHERE id=?",
            [$hash, $id]
        );

        $this->audit->logUserAction('password_reset', $id, $user['username']);
        $this->success(null, 'Password reset. User must change it on next login.');
    }

    public function unlock(array $params): void
    {
        $this->requirePermission('users.unlock');
        $this->validateCsrf();

        $id   = (int)$params['id'];
        $user = $this->db->fetchOne("SELECT * FROM users WHERE id=?", [$id]);
        if (!$user) { $this->error('User not found.'); return; }

        $this->db->execute(
            "UPDATE users SET is_locked=0,failed_attempts=0,locked_until=NULL WHERE id=?",
            [$id]
        );

        $this->audit->logUserAction('account_unlocked', $id, $user['username']);

        if ($this->isAjax()) { $this->success(null, 'Account unlocked.'); return; }
        Session::flash('success', "Account '{$user['username']}' unlocked.");
        $this->redirectRoute('/users');
    }

    public function destroy(array $params): void
    {
        $this->requirePermission('users.delete');
        $this->validateCsrf();

        $id   = (int)$params['id'];
        if ($id === Auth::id()) { $this->error('You cannot delete your own account.'); return; }

        $user = $this->db->fetchOne("SELECT * FROM users WHERE id=?", [$id]);
        $this->db->execute("UPDATE users SET deleted_at=NOW(),is_active=0 WHERE id=?", [$id]);

        $this->audit->logUserAction('user_deleted', $id, $user['username']);
        if ($this->isAjax()) { $this->success(null, 'User deleted.'); return; }
        Session::flash('success', 'User deleted.');
        $this->redirectRoute('/users');
    }
}

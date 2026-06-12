<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\Auth;
use App\Helpers\Session;
use App\Helpers\CSRF;
use App\Helpers\Validator;
use App\Services\AuditService;
use App\Services\NotificationService;

class AuthController extends Controller
{
    private AuditService $audit;
    private NotificationService $notifications;

    public function __construct()
    {
        parent::__construct();
        $this->audit         = new AuditService();
        $this->notifications = new NotificationService();
    }

    // ─── Show login form ─────────────────────────────────────
    public function showLogin(): void
    {
        $this->view('auth.login', ['pageTitle' => 'Login – ' . APP_NAME], null);
    }

    // ─── Handle login ────────────────────────────────────────
    public function login(): void
    {
        $this->validateCsrf();

        $username = trim($this->post('username', ''));
        $password = $this->post('password', '');

        $v = Validator::make(['username' => $username, 'password' => $password], [
            'username' => 'required|min:3|max:50',
            'password' => 'required|min:6',
        ]);

        if ($v->fails()) {
            Session::flash('error', 'Please enter your username and password.');
            $this->redirectRoute('/login');
            return;
        }

        // Fetch user
        $user = $this->db->fetchOne(
            "SELECT u.*, r.name AS role_name, r.display_name AS role_display
             FROM users u JOIN roles r ON r.id = u.role_id
             WHERE (u.username = ? OR u.email = ?) AND u.deleted_at IS NULL
             LIMIT 1",
            [$username, $username]
        );

        // Check if locked
        if ($user && $user['is_locked']) {
            if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
                $remaining = ceil((strtotime($user['locked_until']) - time()) / 60);
                Session::flash('error', "Account locked. Try again in {$remaining} minute(s).");
                $this->audit->logFailedLogin($username);
                $this->redirectRoute('/login');
                return;
            }
            // Unlock if lock expired
            $this->db->execute(
                "UPDATE users SET is_locked = 0, failed_attempts = 0, locked_until = NULL WHERE id = ?",
                [$user['id']]
            );
            $user['is_locked'] = 0;
        }

        // Validate credentials
        if (!$user || !$user['is_active'] || !password_verify($password, $user['password_hash'])) {
            if ($user) {
                $attempts = (int)$user['failed_attempts'] + 1;
                $locked   = 0;
                $lockedUntil = null;

                if ($attempts >= MAX_LOGIN_ATTEMPTS) {
                    $locked      = 1;
                    $lockedUntil = date('Y-m-d H:i:s', time() + LOCKOUT_MINUTES * 60);
                    $this->notifications->notifyAccountLocked($user['username']);
                }

                $this->db->execute(
                    "UPDATE users SET failed_attempts = ?, is_locked = ?, locked_until = ? WHERE id = ?",
                    [$attempts, $locked, $lockedUntil, $user['id']]
                );

                $this->notifications->notifyFailedLogin($username, $attempts);
                $this->audit->logFailedLogin($username);

                if ($locked) {
                    Session::flash('error', "Too many failed attempts. Account locked for " . LOCKOUT_MINUTES . " minutes.");
                } else {
                    $remaining = MAX_LOGIN_ATTEMPTS - $attempts;
                    Session::flash('error', "Invalid credentials. {$remaining} attempt(s) remaining.");
                }
            } else {
                Session::flash('error', 'Invalid username or password.');
                $this->audit->logFailedLogin($username);
            }

            $this->redirectRoute('/login');
            return;
        }

        // Successful login
        Auth::login($user);
        $this->audit->logLogin($user['id'], $user['username']);

        // Must change password?
        if ($user['must_change_password']) {
            Session::flash('warning', 'You must change your password before continuing.');
            $this->redirectRoute('/profile/change-password');
            return;
        }

        $this->redirectRoute('/dashboard');
    }

    // ─── Logout ──────────────────────────────────────────────
    public function logout(): void
    {
        if (Auth::check()) {
            $user = Auth::user();
            $this->audit->logLogout($user['id'], $user['username']);
            Auth::logout();
        }
        Session::flash('success', 'You have been logged out successfully.');
        $this->redirectRoute('/login');
    }

    // ─── Profile ─────────────────────────────────────────────
    public function showProfile(): void
    {
        $user = Auth::user();
        $this->view('auth.profile', [
            'pageTitle' => 'My Profile',
            'user'      => $user,
        ]);
    }

    public function updateProfile(): void
    {
        $this->validateCsrf();

        $user = Auth::user();
        $data = [
            'full_name' => trim($this->post('full_name', '')),
            'email'     => trim($this->post('email', '')),
            'phone'     => trim($this->post('phone', '')),
        ];

        $v = Validator::make($data, [
            'full_name' => 'required|min:2|max:150',
            'email'     => 'required|email|max:150',
        ]);

        if ($v->fails()) {
            Session::flash('error', implode(' ', array_merge(...array_values($v->errors()))));
            $this->redirectRoute('/profile');
            return;
        }

        // Check email uniqueness
        $emailExists = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM users WHERE email = ? AND id != ?",
            [$data['email'], $user['id']]
        );

        if ($emailExists) {
            Session::flash('error', 'That email address is already in use.');
            $this->redirectRoute('/profile');
            return;
        }

        $this->db->execute(
            "UPDATE users SET full_name = ?, email = ?, phone = ? WHERE id = ?",
            [$data['full_name'], $data['email'], $data['phone'], $user['id']]
        );

        $this->audit->log('profile_updated', 'users', "User updated their profile.", [], $data, 'user', $user['id']);
        Session::flash('success', 'Profile updated successfully.');
        $this->redirectRoute('/profile');
    }

    // ─── Change password ─────────────────────────────────────
    public function showChangePassword(): void
    {
        $this->view('auth.change_password', ['pageTitle' => 'Change Password']);
    }

    public function changePassword(): void
    {
        $this->validateCsrf();

        $user        = Auth::user();
        $current     = $this->post('current_password', '');
        $newPass     = $this->post('new_password', '');
        $confirm     = $this->post('new_password_confirmation', '');

        if (!password_verify($current, $user['password_hash'])) {
            Session::flash('error', 'Current password is incorrect.');
            $this->redirectRoute('/profile/change-password');
            return;
        }

        $v = Validator::make(['new_password' => $newPass, 'new_password_confirmation' => $confirm], [
            'new_password' => 'required|min:8|confirmed',
        ]);

        if ($v->fails()) {
            Session::flash('error', $v->firstError('new_password'));
            $this->redirectRoute('/profile/change-password');
            return;
        }

        $hash = password_hash($newPass, PASSWORD_ARGON2ID, [
            'memory_cost' => ARGON_MEMORY,
            'time_cost'   => ARGON_TIME,
            'threads'     => ARGON_THREADS,
        ]);

        $this->db->execute(
            "UPDATE users SET password_hash = ?, must_change_password = 0, password_changed_at = NOW() WHERE id = ?",
            [$hash, $user['id']]
        );

        $this->audit->log('password_changed', 'auth', "User changed their password.", [], [], 'user', $user['id'], 'medium');
        Session::flash('success', 'Password changed successfully.');
        $this->redirectRoute('/dashboard');
    }
}

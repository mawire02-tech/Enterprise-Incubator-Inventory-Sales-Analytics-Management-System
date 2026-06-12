<?php

namespace App\Helpers;

use App\Core\Database;
use App\Core\Logger;

/**
 * Auth Helper
 * Manages user authentication state and permission checks.
 */
class Auth
{
    private static ?array $user        = null;
    private static ?array $permissions = null;

    // ─── Authentication ─────────────────────────────────────

    public static function check(): bool
    {
        return isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] > 0;
    }

    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }

        if (self::$user === null) {
            $db = Database::getInstance();
            self::$user = $db->fetchOne(
                "SELECT u.*, r.name AS role_name, r.display_name AS role_display
                 FROM users u
                 JOIN roles r ON r.id = u.role_id
                 WHERE u.id = ? AND u.is_active = 1 AND u.deleted_at IS NULL",
                [$_SESSION['user_id']]
            ) ?: null;
        }

        return self::$user;
    }

    public static function id(): ?int
    {
        return self::check() ? (int)$_SESSION['user_id'] : null;
    }

    public static function role(): ?string
    {
        return self::user()['role_name'] ?? null;
    }

    public static function isAdmin(): bool
    {
        return self::role() === 'administrator';
    }

    // ─── Login ──────────────────────────────────────────────

    public static function login(array $user): void
    {
        session_regenerate_id(true);

        $_SESSION['user_id']    = $user['id'];
        $_SESSION['username']   = $user['username'];
        $_SESSION['role']       = $user['role_name'] ?? '';
        $_SESSION['login_time'] = time();
        $_SESSION['last_active'] = time();

        // Update last login
        $db = Database::getInstance();
        $db->execute(
            "UPDATE users SET last_login_at = NOW(), last_login_ip = ?, failed_attempts = 0 WHERE id = ?",
            [self::getClientIp(), $user['id']]
        );

        self::$user        = null;
        self::$permissions = null;

        Logger::info("User logged in: {$user['username']}", ['ip' => self::getClientIp()]);
    }

    public static function logout(): void
    {
        $username = $_SESSION['username'] ?? 'unknown';

        // Delete session record
        $db = Database::getInstance();
        $db->execute("DELETE FROM user_sessions WHERE id = ?", [session_id()]);

        session_unset();
        session_destroy();

        self::$user        = null;
        self::$permissions = null;

        Logger::info("User logged out: {$username}");
    }

    // ─── Permissions (RBAC + PBAC) ──────────────────────────

    public static function can(string $permission): bool
    {
        if (!self::check()) {
            return false;
        }

        if (self::isAdmin()) {
            return true;
        }

        return in_array($permission, self::getPermissions(), true);
    }

    public static function canAny(array $permissions): bool
    {
        foreach ($permissions as $p) {
            if (self::can($p)) return true;
        }
        return false;
    }

    public static function canAll(array $permissions): bool
    {
        foreach ($permissions as $p) {
            if (!self::can($p)) return false;
        }
        return true;
    }

    public static function getPermissions(): array
    {
        if (self::$permissions !== null) {
            return self::$permissions;
        }

        $user = self::user();
        if (!$user) {
            return self::$permissions = [];
        }

        $db = Database::getInstance();

        // Role permissions
        $rolePerms = $db->fetchAll(
            "SELECT p.name FROM permissions p
             JOIN role_permissions rp ON rp.permission_id = p.id
             WHERE rp.role_id = ?",
            [$user['role_id']]
        );

        // User-specific overrides
        $userPerms = $db->fetchAll(
            "SELECT p.name, up.granted FROM permissions p
             JOIN user_permissions up ON up.permission_id = p.id
             WHERE up.user_id = ?",
            [$user['id']]
        );

        $perms = array_column($rolePerms, 'name');

        foreach ($userPerms as $up) {
            if ($up['granted']) {
                $perms[] = $up['name'];
            } else {
                $perms = array_filter($perms, fn($p) => $p !== $up['name']);
            }
        }

        return self::$permissions = array_values(array_unique($perms));
    }

    // ─── Session security ────────────────────────────────────

    public static function checkSessionTimeout(): bool
    {
        if (!self::check()) {
            return false;
        }

        $timeout  = (int)($_SESSION['timeout'] ?? SESSION_LIFETIME);
        $lastActive = (int)($_SESSION['last_active'] ?? 0);

        if (time() - $lastActive > $timeout) {
            self::logout();
            return false;
        }

        $_SESSION['last_active'] = time();
        return true;
    }

    // ─── Utilities ──────────────────────────────────────────

    public static function getClientIp(): string
    {
        foreach (['HTTP_CLIENT_IP','HTTP_X_FORWARDED_FOR','REMOTE_ADDR'] as $key) {
            if (!empty($_SERVER[$key])) {
                return trim(explode(',', $_SERVER[$key])[0]);
            }
        }
        return '0.0.0.0';
    }

    public static function getUserAgent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? '';
    }

    public static function getDeviceType(): string
    {
        $ua = self::getUserAgent();
        if (preg_match('/Mobile|Android|iPhone|iPad/i', $ua)) {
            return str_contains(strtolower($ua), 'ipad') ? 'tablet' : 'mobile';
        }
        return 'desktop';
    }
}

<?php

namespace App\Helpers;

/**
 * CSRF Protection Helper
 */
class CSRF
{
    private const KEY = '_csrf_token';

    public static function generate(): string
    {
        if (empty($_SESSION[self::KEY])) {
            $_SESSION[self::KEY] = bin2hex(random_bytes(CSRF_TOKEN_LENGTH));
        }
        return $_SESSION[self::KEY];
    }

    public static function token(): string
    {
        return self::generate();
    }

    public static function field(): string
    {
        return '<input type="hidden" name="_csrf_token" value="' . self::token() . '">';
    }

    public static function validate(): bool
    {
        $token  = $_POST['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        $stored = $_SESSION[self::KEY] ?? '';

        if (empty($token) || empty($stored)) {
            return false;
        }

        return hash_equals($stored, $token);
    }

    public static function refresh(): void
    {
        $_SESSION[self::KEY] = bin2hex(random_bytes(CSRF_TOKEN_LENGTH));
    }
}

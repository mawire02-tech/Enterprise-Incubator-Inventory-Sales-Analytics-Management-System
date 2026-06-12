<?php

namespace App\Helpers;

/**
 * Session Helper
 */
class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            $secure   = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
            $sameSite = 'Strict';

            session_set_cookie_params([
                'lifetime' => 0,
                'path'     => '/',
                'domain'   => '',
                'secure'   => $secure,
                'httponly' => true,
                'samesite' => $sameSite,
            ]);
            session_name(SESSION_NAME);
            session_start();
        }
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function flash(string $key, mixed $value): void
    {
        $_SESSION['_flash'][$key] = $value;
    }

    public static function getFlash(string $key, mixed $default = null): mixed
    {
        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }

    public static function allFlash(): array
    {
        $flash = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);
        return $flash;
    }

    public static function destroy(): void
    {
        session_unset();
        session_destroy();
    }
}


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


/**
 * Validator Helper
 */
class Validator
{
    private array $errors = [];
    private array $data   = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public static function make(array $data, array $rules): self
    {
        $v = new self($data);
        $v->validate($rules);
        return $v;
    }

    public function validate(array $rules): bool
    {
        $this->errors = [];

        foreach ($rules as $field => $ruleString) {
            $ruleList = is_array($ruleString) ? $ruleString : explode('|', $ruleString);
            $value    = $this->data[$field] ?? null;

            foreach ($ruleList as $rule) {
                $this->applyRule($field, $value, $rule);
            }
        }

        return empty($this->errors);
    }

    private function applyRule(string $field, mixed $value, string $rule): void
    {
        [$ruleName, $param] = array_pad(explode(':', $rule, 2), 2, null);
        $label = ucwords(str_replace('_', ' ', $field));

        switch ($ruleName) {
            case 'required':
                if ($value === null || $value === '' || $value === []) {
                    $this->errors[$field][] = "{$label} is required.";
                }
                break;
            case 'min':
                if (strlen((string)$value) < (int)$param) {
                    $this->errors[$field][] = "{$label} must be at least {$param} characters.";
                }
                break;
            case 'max':
                if (strlen((string)$value) > (int)$param) {
                    $this->errors[$field][] = "{$label} must not exceed {$param} characters.";
                }
                break;
            case 'min_val':
                if ((float)$value < (float)$param) {
                    $this->errors[$field][] = "{$label} must be at least {$param}.";
                }
                break;
            case 'max_val':
                if ((float)$value > (float)$param) {
                    $this->errors[$field][] = "{$label} must not exceed {$param}.";
                }
                break;
            case 'email':
                if ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->errors[$field][] = "{$label} must be a valid email address.";
                }
                break;
            case 'numeric':
                if ($value !== null && $value !== '' && !is_numeric($value)) {
                    $this->errors[$field][] = "{$label} must be numeric.";
                }
                break;
            case 'integer':
                if ($value !== null && $value !== '' && !ctype_digit((string)$value)) {
                    $this->errors[$field][] = "{$label} must be an integer.";
                }
                break;
            case 'in':
                $allowed = explode(',', $param);
                if ($value !== null && $value !== '' && !in_array($value, $allowed, true)) {
                    $this->errors[$field][] = "{$label} has an invalid value.";
                }
                break;
            case 'date':
                if ($value && !\DateTime::createFromFormat('Y-m-d', $value)) {
                    $this->errors[$field][] = "{$label} must be a valid date (YYYY-MM-DD).";
                }
                break;
            case 'regex':
                if ($value && !preg_match($param, $value)) {
                    $this->errors[$field][] = "{$label} format is invalid.";
                }
                break;
            case 'confirmed':
                if ($value !== ($this->data[$field . '_confirmation'] ?? null)) {
                    $this->errors[$field][] = "{$label} confirmation does not match.";
                }
                break;
            case 'phone_zw':
                if ($value && !preg_match('/^\+263[0-9]{9}$/', $value)) {
                    $this->errors[$field][] = "{$label} must be a valid Zimbabwe phone (+263XXXXXXXXX).";
                }
                break;
        }
    }

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    public function passes(): bool
    {
        return empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(string $field): ?string
    {
        return $this->errors[$field][0] ?? null;
    }
}

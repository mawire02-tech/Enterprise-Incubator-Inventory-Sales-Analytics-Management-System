<?php

namespace App\Helpers;

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

<?php

namespace App\Core;

use App\Helpers\Auth;
use App\Helpers\Session;
use App\Helpers\CSRF;

/**
 * Base Controller
 */
abstract class Controller
{
    protected Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ─── View rendering ─────────────────────────────────────

    protected function view(string $view, array $data = [], ?string $layout = 'main'): void
    {
        extract($data);

        // Current user for all views
        $currentUser = Auth::user();
        $pageTitle   = $data['pageTitle'] ?? APP_NAME;

        if ($layout === null) {
            // No layout, render view directly
            $viewFile = VIEWS_PATH . '/' . str_replace('.', '/', $view) . '.php';
            if (!file_exists($viewFile)) {
                throw new \RuntimeException("View not found: {$viewFile}");
            }
            require $viewFile;
            return;
        }

        // Capture view content
        ob_start();
        $viewFile = VIEWS_PATH . '/' . str_replace('.', '/', $view) . '.php';
        if (!file_exists($viewFile)) {
            throw new \RuntimeException("View not found: {$viewFile}");
        }
        require $viewFile;
        $content = ob_get_clean();

        // Render layout
        $layoutFile = VIEWS_PATH . '/layouts/' . $layout . '.php';
        if (!file_exists($layoutFile)) {
            throw new \RuntimeException("Layout not found: {$layoutFile}");
        }
        require $layoutFile;
    }

    // ─── JSON responses ─────────────────────────────────────

    protected function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    protected function success(mixed $data = null, string $message = 'Success', int $status = 200): void
    {
        $this->json(['success' => true, 'message' => $message, 'data' => $data], $status);
    }

    protected function error(string $message, int $status = 400, mixed $errors = null): void
    {
        $this->json(['success' => false, 'message' => $message, 'errors' => $errors], $status);
    }

    // ─── Redirect ───────────────────────────────────────────

    protected function redirect(string $url, int $code = 302): void
    {
        header("Location: {$url}", true, $code);
        exit;
    }

    protected function redirectRoute(string $path, int $code = 302): void
    {
        $this->redirect(APP_URL . '/' . ltrim($path, '/'), $code);
    }

    protected function back(): void
    {
        $ref = $_SERVER['HTTP_REFERER'] ?? APP_URL . '/dashboard';
        $this->redirect($ref);
    }

    // ─── Request helpers ────────────────────────────────────

    protected function input(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    protected function post(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $default;
    }

    protected function get(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }

    protected function all(): array
    {
        return array_merge($_GET, $_POST);
    }

    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    protected function isAjax(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    protected function isJson(): bool
    {
        $ct = $_SERVER['CONTENT_TYPE'] ?? '';
        return str_contains($ct, 'application/json');
    }

    protected function jsonBody(): array
    {
        $body = file_get_contents('php://input');
        return json_decode($body, true) ?? [];
    }

    // ─── CSRF ────────────────────────────────────────────────

    protected function validateCsrf(): void
    {
        if (!CSRF::validate()) {
            if ($this->isAjax()) {
                $this->error('CSRF token mismatch.', 403);
            }
            Session::flash('error', 'Security token expired. Please try again.');
            $this->back();
        }
    }

    // ─── Authorization ──────────────────────────────────────

    protected function requirePermission(string $permission): void
    {
        if (!Auth::can($permission)) {
            if ($this->isAjax()) {
                $this->error('Permission denied.', 403);
            }
            $this->redirectRoute('/403');
        }
    }

    protected function requireAuth(): void
    {
        if (!Auth::check()) {
            if ($this->isAjax()) {
                $this->error('Unauthenticated.', 401);
            }
            $this->redirectRoute('/login');
        }
    }

    // ─── Flash messages ─────────────────────────────────────

    protected function flash(string $type, string $message): void
    {
        Session::flash($type, $message);
    }

    // ─── Pagination helper ──────────────────────────────────

    protected function paginate(int $total, int $perPage = PER_PAGE_DEFAULT): array
    {
        $perPage  = min(max(1, $perPage), PER_PAGE_MAX);
        $page     = max(1, (int)($this->get('page', 1)));
        $pages    = (int)ceil($total / $perPage);
        $offset   = ($page - 1) * $perPage;

        return [
            'total'    => $total,
            'per_page' => $perPage,
            'page'     => $page,
            'pages'    => $pages,
            'offset'   => $offset,
            'has_prev' => $page > 1,
            'has_next' => $page < $pages,
        ];
    }
}

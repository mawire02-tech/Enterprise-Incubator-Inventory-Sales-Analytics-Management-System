<?php

namespace App\Core;

/**
 * Router
 * Parses URL, dispatches to Controller@action with middleware support.
 */
class Router
{
    private array $routes = [];
    private array $middlewareGroups = [];
    private string $prefix = '';
    private array $currentMiddleware = [];

    // ─── Route registration ─────────────────────────────────

    public function get(string $uri, string $handler, array $middleware = []): void
    {
        $this->addRoute('GET', $uri, $handler, $middleware);
    }

    public function post(string $uri, string $handler, array $middleware = []): void
    {
        $this->addRoute('POST', $uri, $handler, $middleware);
    }

    public function put(string $uri, string $handler, array $middleware = []): void
    {
        $this->addRoute('PUT', $uri, $handler, $middleware);
    }

    public function delete(string $uri, string $handler, array $middleware = []): void
    {
        $this->addRoute('DELETE', $uri, $handler, $middleware);
    }

    public function any(string $uri, string $handler, array $middleware = []): void
    {
        foreach (['GET','POST','PUT','DELETE','PATCH'] as $method) {
            $this->addRoute($method, $uri, $handler, $middleware);
        }
    }

    private function addRoute(string $method, string $uri, string $handler, array $middleware): void
    {
        $uri = $this->prefix . '/' . ltrim($uri, '/');
        $uri = '/' . ltrim($uri, '/');

        $this->routes[] = [
            'method'     => $method,
            'uri'        => $uri,
            'handler'    => $handler,
            'middleware' => array_merge($this->currentMiddleware, $middleware),
            'pattern'    => $this->buildPattern($uri),
        ];
    }

    public function group(array $options, callable $callback): void
    {
        $prevPrefix     = $this->prefix;
        $prevMiddleware = $this->currentMiddleware;

        if (isset($options['prefix'])) {
            $this->prefix .= '/' . trim($options['prefix'], '/');
        }
        if (isset($options['middleware'])) {
            $mw = is_array($options['middleware']) ? $options['middleware'] : [$options['middleware']];
            $this->currentMiddleware = array_merge($this->currentMiddleware, $mw);
        }

        $callback($this);

        $this->prefix           = $prevPrefix;
        $this->currentMiddleware = $prevMiddleware;
    }

    // ─── Route resolution ───────────────────────────────────

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri    = $this->getUri();

        // Support method override via POST _method
        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $params = [];
            if (preg_match($route['pattern'], $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                // Run middleware
                foreach ($route['middleware'] as $mwClass) {
                    $mw = $this->resolveMiddleware($mwClass);
                    if ($mw !== null) {
                        $result = $mw->handle();
                        if ($result === false) {
                            return;
                        }
                    }
                }

                // Dispatch controller
                [$controllerClass, $action] = explode('@', $route['handler']);
                $fullClass = "App\\Controllers\\{$controllerClass}";

                if (!class_exists($fullClass)) {
                    $this->abort(500, "Controller {$fullClass} not found.");
                    return;
                }

                $controller = new $fullClass();

                if (!method_exists($controller, $action)) {
                    $this->abort(500, "Action {$action} not found in {$fullClass}.");
                    return;
                }

                $controller->$action($params);
                return;
            }
        }

        $this->abort(404, "Route not found: {$uri}");
    }

    // ─── Helpers ────────────────────────────────────────────

    private function getUri(): string
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Strip public/ base path if running in subdirectory
        $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
        if ($base && str_starts_with($uri, $base)) {
            $uri = substr($uri, strlen($base));
        }

        return '/' . ltrim($uri ?: '/', '/');
    }

    private function buildPattern(string $uri): string
    {
        $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $uri);
        $pattern = preg_replace('/\{(\w+)\?\}/', '(?P<$1>[^/]*)', $pattern);
        return '#^' . $pattern . '$#';
    }

    private function resolveMiddleware(string $mwClass): ?object
    {
        $fullClass = "App\\Middleware\\{$mwClass}";
        if (class_exists($fullClass)) {
            return new $fullClass();
        }
        return null;
    }

    private function abort(int $code, string $message): void
    {
        http_response_code($code);
        if ($code === 404) {
            require VIEWS_PATH . '/errors/404.php';
        } elseif ($code === 403) {
            require VIEWS_PATH . '/errors/403.php';
        } else {
            echo "<h1>Error {$code}</h1><p>" . htmlspecialchars($message) . "</p>";
        }
    }
}

<?php
/**
 * Enterprise Incubator Inventory, Sales & Analytics System
 * Public entry point
 */

declare(strict_types=1);

// ─── Load configuration ─────────────────────────────────────
require_once dirname(__DIR__) . '/config/config.php';

// ─── Error handling ─────────────────────────────────────────
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

set_exception_handler(function (\Throwable $e) {
    \App\Core\Logger::exception($e);
    if (APP_DEBUG) {
        http_response_code(500);
        echo '<pre style="background:#1e1e2e;color:#f38ba8;padding:20px;font-family:monospace;">';
        echo '<strong>Unhandled Exception</strong>' . PHP_EOL;
        echo htmlspecialchars($e->getMessage()) . PHP_EOL;
        echo htmlspecialchars($e->getTraceAsString());
        echo '</pre>';
    } else {
        http_response_code(500);
        echo '<h1>500 – Internal Server Error</h1><p>Something went wrong. Please try again.</p>';
    }
});

set_error_handler(function (int $severity, string $message, string $file, int $line): bool {
    if (!(error_reporting() & $severity)) return false;
    throw new \ErrorException($message, 0, $severity, $file, $line);
});

// ─── PSR-4 Autoloader ────────────────────────────────────────
spl_autoload_register(function (string $class): void {
    if (str_starts_with($class, 'App\\')) {
        $rel  = str_replace(['App\\', '\\'], ['', '/'], $class);
        $file = ROOT_PATH . '/app/' . $rel . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
        // Fallback: combined Helpers.php contains Session, CSRF, Validator
        $helpersFile = ROOT_PATH . '/app/Helpers/Helpers.php';
        if (file_exists($helpersFile)) {
            require_once $helpersFile;
        }
    }
});

// ─── Session ─────────────────────────────────────────────────
\App\Helpers\Session::start();

// ─── Security headers ────────────────────────────────────────
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
if (!APP_DEBUG) {
    header('Content-Security-Policy: default-src \'self\'; script-src \'self\' \'unsafe-inline\' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; style-src \'self\' \'unsafe-inline\' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; img-src \'self\' data:; font-src \'self\' https://cdnjs.cloudflare.com;');
}

// ─── CSRF token init ─────────────────────────────────────────
\App\Helpers\CSRF::generate();

// ─── Route & dispatch ────────────────────────────────────────
$router = require ROOT_PATH . '/routes/web.php';
$router->dispatch();

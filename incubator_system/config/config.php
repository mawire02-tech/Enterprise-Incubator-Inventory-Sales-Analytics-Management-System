<?php
/**
 * Application Configuration
 * Enterprise Incubator Inventory, Sales & Analytics System
 */

// ─── Environment ────────────────────────────────────────────
define('APP_ENV',     getenv('APP_ENV')  ?: 'development'); // development | production
define('APP_DEBUG',   APP_ENV === 'development');
define('APP_VERSION', '1.0.0');
define('APP_NAME',    'Enterprise Incubator System');
define('APP_URL',     getenv('APP_URL')  ?: 'http://localhost/incubator_system/public');

// ─── Paths ───────────────────────────────────────────────────
define('ROOT_PATH',    dirname(__DIR__));
define('APP_PATH',     ROOT_PATH . '/app');
define('CONFIG_PATH',  ROOT_PATH . '/config');
define('PUBLIC_PATH',  ROOT_PATH . '/public');
define('STORAGE_PATH', ROOT_PATH . '/storage');
define('VIEWS_PATH',   APP_PATH  . '/Views');

// ─── Database ───────────────────────────────────────────────
define('DB_HOST',    getenv('DB_HOST')    ?: '127.0.0.1');
define('DB_PORT',    getenv('DB_PORT')    ?: '3306');
define('DB_NAME',    getenv('DB_NAME')    ?: 'incubator_system');
define('DB_USER',    getenv('DB_USER')    ?: 'root');
define('DB_PASS',    getenv('DB_PASS')    ?: '');
define('DB_CHARSET', 'utf8mb4');

// ─── Security ───────────────────────────────────────────────
define('APP_KEY',           getenv('APP_KEY') ?: 'change_this_32_char_secret_key!!');
define('SESSION_NAME',      'EISAMS_SESSION');
define('SESSION_LIFETIME',  1800);           // 30 minutes idle timeout
define('CSRF_TOKEN_LENGTH', 32);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_MINUTES',   30);
define('PASSWORD_MIN_LENGTH', 8);

// ─── Argon2id settings ──────────────────────────────────────
define('ARGON_MEMORY',      65536);
define('ARGON_TIME',        4);
define('ARGON_THREADS',     1);

// ─── Pagination ─────────────────────────────────────────────
define('PER_PAGE_DEFAULT', 25);
define('PER_PAGE_MAX',     100);

// ─── File Uploads ───────────────────────────────────────────
define('UPLOAD_PATH',     STORAGE_PATH . '/uploads');
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5 MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);

// ─── Reports / Exports ──────────────────────────────────────
define('EXPORTS_PATH',  STORAGE_PATH . '/exports');
define('REPORTS_PATH',  STORAGE_PATH . '/exports');
define('BACKUPS_PATH',  STORAGE_PATH . '/backups');

// ─── Logging ────────────────────────────────────────────────
define('LOG_PATH',  STORAGE_PATH . '/logs');
define('LOG_LEVEL', APP_DEBUG ? 'debug' : 'error'); // debug|info|warning|error

// ─── Timezone ───────────────────────────────────────────────
define('APP_TIMEZONE', 'Africa/Harare');
date_default_timezone_set(APP_TIMEZONE);

// ─── Currency ───────────────────────────────────────────────
define('CURRENCY_CODE',   'USD');
define('CURRENCY_SYMBOL', '$');

// ─── Real-time ──────────────────────────────────────────────
define('SSE_RETRY_MS',    3000);   // SSE reconnect interval
define('WS_PORT',         8080);   // WebSocket server port (if using)

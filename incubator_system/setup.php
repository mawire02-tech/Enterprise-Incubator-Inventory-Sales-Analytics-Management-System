#!/usr/bin/env php
<?php
/**
 * Setup / Installation Script
 * Run once from the project root: php setup.php
 */

define('ROOT_PATH', __DIR__);
require_once __DIR__ . '/config/config.php';

echo "\n========================================================\n";
echo "  Enterprise Incubator System — Setup\n";
echo "========================================================\n\n";

// ─── Connect to MySQL ────────────────────────────────────────
try {
    $pdo = new PDO(
        sprintf('mysql:host=%s;port=%s;charset=utf8mb4', DB_HOST, DB_PORT),
        DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "✓  Connected to MySQL\n";
} catch (PDOException $e) {
    die("✗  Cannot connect to MySQL: " . $e->getMessage() . "\n");
}

// ─── Create database ─────────────────────────────────────────
$pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$pdo->exec("USE `" . DB_NAME . "`");
echo "✓  Database '" . DB_NAME . "' ready\n";

// ─── Run migrations ──────────────────────────────────────────
$migrations = glob(__DIR__ . '/database/migrations/*.sql');
sort($migrations);

foreach ($migrations as $file) {
    $sql = file_get_contents($file);
    // Split on semicolons, skip empty
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    foreach ($statements as $stmt) {
        if (!empty($stmt) && !str_starts_with($stmt, '--')) {
            try {
                $pdo->exec($stmt);
            } catch (PDOException $e) {
                // Ignore "already exists" errors
                if (!str_contains($e->getMessage(), 'already exists') &&
                    !str_contains($e->getMessage(), 'Duplicate')) {
                    echo "  WARN: " . $e->getMessage() . " [" . substr($stmt, 0, 60) . "…]\n";
                }
            }
        }
    }
    echo "✓  Migration: " . basename($file) . "\n";
}

// ─── Run seeds ───────────────────────────────────────────────
$seeds = glob(__DIR__ . '/database/seeds/*.sql');
sort($seeds);

foreach ($seeds as $file) {
    $sql = file_get_contents($file);
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    foreach ($statements as $stmt) {
        if (!empty($stmt) && !str_starts_with($stmt, '--')) {
            try {
                $pdo->exec($stmt);
            } catch (PDOException $e) {
                if (!str_contains($e->getMessage(), 'Duplicate entry') &&
                    !str_contains($e->getMessage(), 'already exists')) {
                    echo "  WARN: " . $e->getMessage() . "\n";
                }
            }
        }
    }
    echo "✓  Seed:      " . basename($file) . "\n";
}

// ─── Create storage directories ──────────────────────────────
$dirs = [
    ROOT_PATH . '/storage/logs',
    ROOT_PATH . '/storage/backups',
    ROOT_PATH . '/storage/exports',
    ROOT_PATH . '/storage/uploads',
];
foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
        echo "✓  Created: " . str_replace(ROOT_PATH.'/', '', $dir) . "\n";
    }
}

// ─── .gitignore for storage ──────────────────────────────────
$gitignore = ROOT_PATH . '/storage/.gitignore';
if (!file_exists($gitignore)) {
    file_put_contents($gitignore, "*\n!.gitignore\n");
}

// ─── Hash admin password properly ───────────────────────────
echo "\n─── Updating admin password hash ───────────────────────\n";
$hash = password_hash('Admin@1234', PASSWORD_ARGON2ID, [
    'memory_cost' => 65536, 'time_cost' => 4, 'threads' => 1,
]);
$stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE username = 'admin'");
$stmt->execute([$hash]);
echo "✓  Admin password hash updated (Argon2id)\n";

// ─── Hash demo user passwords ────────────────────────────────
$demoHash = password_hash('Password@123', PASSWORD_ARGON2ID, [
    'memory_cost' => 65536, 'time_cost' => 4, 'threads' => 1,
]);
$stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE username IN ('manager1','sales1','clerk1')");
$stmt->execute([$demoHash]);
echo "✓  Demo user password hashes updated\n";

// ─── Done ────────────────────────────────────────────────────
echo "\n========================================================\n";
echo "  ✓  Setup complete!\n\n";
echo "  Access the system at: " . APP_URL . "\n\n";
echo "  Default Credentials:\n";
echo "  ┌───────────────┬─────────────────┬──────────────────┐\n";
echo "  │ Role          │ Username        │ Password         │\n";
echo "  ├───────────────┼─────────────────┼──────────────────┤\n";
echo "  │ Administrator │ admin           │ Admin@1234       │\n";
echo "  │ Manager       │ manager1        │ Password@123     │\n";
echo "  │ Sales Officer │ sales1          │ Password@123     │\n";
echo "  │ Stock Clerk   │ clerk1          │ Password@123     │\n";
echo "  └───────────────┴─────────────────┴──────────────────┘\n\n";
echo "  ⚠  IMPORTANT: Change all passwords after first login!\n";
echo "========================================================\n\n";

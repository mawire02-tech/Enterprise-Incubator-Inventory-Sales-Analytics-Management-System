<?php
/**
 * One-Time Admin Password Reset
 * ─────────────────────────────
 * 1. Place this file in your project ROOT (same folder as setup.php)
 * 2. Open it in your browser: http://localhost/incubator_system/reset_password.php
 * 3. DELETE this file immediately after use.
 */

// ── Load app config so we use the same DB credentials ────────
define('BASE_PATH', __DIR__);
require_once __DIR__ . '/config/config.php';

$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username    = trim($_POST['username'] ?? '');
    $newPassword = $_POST['new_password'] ?? '';
    $confirm     = $_POST['confirm_password'] ?? '';
    $secretKey   = $_POST['secret_key'] ?? '';

    // Basic safety gate — change this value before uploading
    $expectedKey = 'reset-' . date('Ymd'); // e.g. "reset-20260610" — changes daily

    if ($secretKey !== $expectedKey) {
        $message = '❌ Invalid secret key.';
    } elseif (strlen($newPassword) < 8) {
        $message = '❌ Password must be at least 8 characters.';
    } elseif ($newPassword !== $confirm) {
        $message = '❌ Passwords do not match.';
    } else {
        try {
            $pdo = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER,
                DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );

            // Check user exists
            $stmt = $pdo->prepare("SELECT id, username FROM users WHERE username = ? AND deleted_at IS NULL LIMIT 1");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                $message = '❌ Username not found.';
            } else {
                $hash = password_hash($newPassword, PASSWORD_ARGON2ID, [
                    'memory_cost' => 65536,
                    'time_cost'   => 4,
                    'threads'     => 1,
                ]);

                $upd = $pdo->prepare(
                    "UPDATE users
                     SET password_hash = ?,
                         must_change_password = 0,
                         failed_attempts = 0,
                         is_locked = 0,
                         locked_until = NULL,
                         password_changed_at = NOW()
                     WHERE id = ?"
                );
                $upd->execute([$hash, $user['id']]);

                $success = true;
                $message = '✅ Password updated for <strong>' . htmlspecialchars($user['username']) . '</strong>. Delete this file now!';
            }
        } catch (PDOException $e) {
            $message = '❌ Database error: ' . htmlspecialchars($e->getMessage());
        }
    }
}

// Today's secret key hint (shown in the form label)
$todayKey = 'reset-' . date('Ymd');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Password Reset</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: system-ui, sans-serif; background: #f1f5f9; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .card { background: #fff; border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,.1); padding: 2rem; width: 100%; max-width: 420px; }
        h1 { font-size: 1.25rem; color: #1e293b; margin-bottom: .25rem; }
        .subtitle { font-size: .8rem; color: #94a3b8; margin-bottom: 1.5rem; }
        label { display: block; font-size: .82rem; font-weight: 600; color: #374151; margin-bottom: .3rem; margin-top: 1rem; }
        input { width: 100%; padding: .55rem .75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: .9rem; outline: none; }
        input:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,.15); }
        button { width: 100%; margin-top: 1.5rem; padding: .65rem; background: #6366f1; color: #fff; border: none; border-radius: 6px; font-size: .95rem; font-weight: 600; cursor: pointer; }
        button:hover { background: #4f46e5; }
        .alert { margin-top: 1rem; padding: .75rem 1rem; border-radius: 6px; font-size: .875rem; }
        .alert.error   { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
        .alert.success { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
        .hint { font-size: .75rem; color: #6b7280; margin-top: .25rem; }
        .warning { background: #fffbeb; border: 1px solid #fcd34d; border-radius: 6px; padding: .6rem .9rem; font-size: .8rem; color: #92400e; margin-bottom: 1rem; }
    </style>
</head>
<body>
<div class="card">
    <h1>🔑 Password Reset</h1>
    <p class="subtitle">Incubator System — one-time use only</p>

    <div class="warning">
        ⚠️ <strong>Delete this file immediately after use.</strong>
        Anyone with access to this URL can reset any account.
    </div>

    <?php if ($message): ?>
    <div class="alert <?= $success ? 'success' : 'error' ?>"><?= $message ?></div>
    <?php endif; ?>

    <?php if (!$success): ?>
    <form method="POST">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required
               value="<?= htmlspecialchars($_POST['username'] ?? 'admin') ?>"
               placeholder="admin">

        <label for="new_password">New Password</label>
        <input type="password" id="new_password" name="new_password" required placeholder="Min 8 characters">

        <label for="confirm_password">Confirm Password</label>
        <input type="password" id="confirm_password" name="confirm_password" required placeholder="Repeat password">

        <label for="secret_key">
            Secret Key
            <span class="hint">Today's key is: <code><?= htmlspecialchars($todayKey) ?></code></span>
        </label>
        <input type="text" id="secret_key" name="secret_key" required placeholder="reset-YYYYMMDD">

        <button type="submit">Reset Password</button>
    </form>
    <?php endif; ?>
</div>
</body>
</html>

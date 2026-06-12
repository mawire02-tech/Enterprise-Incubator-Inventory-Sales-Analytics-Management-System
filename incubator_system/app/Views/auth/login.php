<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — <?= APP_NAME ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        :root { --primary: #2563eb; }
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 50%, #0f172a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: system-ui, sans-serif;
        }
        .login-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,.3);
            width: 100%;
            max-width: 420px;
            padding: 2.5rem 2rem;
        }
        .brand-logo {
            width: 52px; height: 52px;
            background: var(--primary);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.6rem;
            color: #fff;
            margin: 0 auto 1rem;
        }
        .form-control { border-radius: 8px; padding: .65rem 1rem; }
        .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(37,99,235,.15); }
        .btn-login {
            background: var(--primary);
            border: none;
            border-radius: 8px;
            padding: .7rem;
            font-weight: 600;
            letter-spacing: .3px;
        }
        .btn-login:hover { background: #1d4ed8; }
        .input-group-text { border-radius: 0 8px 8px 0; }
        .toggle-pw { cursor: pointer; }
    </style>
</head>
<body>

<div class="login-card">
    <div class="text-center mb-4">
        <div class="brand-logo"><i class="bi bi-boxes"></i></div>
        <h1 class="h5 fw-bold mb-0"><?= APP_NAME ?></h1>
        <p class="text-muted small mb-0">Inventory &amp; Sales Management</p>
    </div>

    <?php $error = \App\Helpers\Session::getFlash('error'); ?>
    <?php $warning = \App\Helpers\Session::getFlash('warning'); ?>
    <?php if ($error): ?>
        <div class="alert alert-danger alert-sm py-2 small d-flex align-items-center gap-2 mb-3">
            <i class="bi bi-exclamation-triangle-fill flex-shrink-0"></i>
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>
    <?php if ($warning): ?>
        <div class="alert alert-warning alert-sm py-2 small d-flex align-items-center gap-2 mb-3">
            <i class="bi bi-exclamation-circle-fill flex-shrink-0"></i>
            <?= htmlspecialchars($warning) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= APP_URL ?>/login" autocomplete="on" novalidate>
        <?= \App\Helpers\CSRF::field() ?>

        <div class="mb-3">
            <label class="form-label fw-semibold" style="font-size:.8rem;text-transform:uppercase;letter-spacing:.4px;color:#64748b">
                Username or Email
            </label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0" style="border-radius:8px 0 0 8px"><i class="bi bi-person text-muted"></i></span>
                <input type="text"
                       name="username"
                       class="form-control border-start-0"
                       style="border-radius:0 8px 8px 0"
                       placeholder="Enter username or email"
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                       autofocus
                       required>
            </div>
        </div>

        <div class="mb-4">
            <label class="form-label fw-semibold" style="font-size:.8rem;text-transform:uppercase;letter-spacing:.4px;color:#64748b">
                Password
            </label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0" style="border-radius:8px 0 0 8px"><i class="bi bi-lock text-muted"></i></span>
                <input type="password"
                       name="password"
                       id="passwordField"
                       class="form-control border-start-0 border-end-0"
                       style="border-radius:0"
                       placeholder="Enter password"
                       required>
                <span class="input-group-text bg-light toggle-pw" onclick="togglePassword()" title="Show/hide">
                    <i class="bi bi-eye text-muted" id="eyeIcon"></i>
                </span>
            </div>
        </div>

        <button type="submit" class="btn btn-login btn-primary w-100 text-white">
            <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
        </button>
    </form>

    <p class="text-center text-muted mt-4 mb-0" style="font-size:.75rem">
        <?= APP_NAME ?> v<?= APP_VERSION ?> &nbsp;|&nbsp;
        &copy; <?= date('Y') ?> All rights reserved
    </p>
</div>

<script>
function togglePassword() {
    const f = document.getElementById('passwordField');
    const i = document.getElementById('eyeIcon');
    if (f.type === 'password') {
        f.type = 'text';
        i.className = 'bi bi-eye-slash text-muted';
    } else {
        f.type = 'password';
        i.className = 'bi bi-eye text-muted';
    }
}
</script>
</body>
</html>

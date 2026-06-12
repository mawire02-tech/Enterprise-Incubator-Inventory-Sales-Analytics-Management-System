<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= \App\Helpers\CSRF::token() ?>">
    <title><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?> — <?= APP_NAME ?></title>

    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <!-- App CSS -->
    <link rel="stylesheet" href="<?= APP_URL ?>/css/app.css">

    <!-- PWA -->
    <link rel="manifest" href="<?= APP_URL ?>/manifest.json">
    <meta name="theme-color" content="#2563eb">
</head>
<body>

<!-- ═══ SIDEBAR ═══════════════════════════════════════════════════════ -->
<nav class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <i class="bi bi-boxes fs-4 me-2 text-primary"></i>
        <span class="brand-text">EIS<span class="text-primary">AMS</span></span>
    </div>

    <div class="sidebar-menu">
        <ul class="nav flex-column">

            <li class="nav-label">MAIN</li>

            <li class="nav-item">
                <a href="<?= APP_URL ?>/dashboard" class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/dashboard') ? 'active' : '' ?>">
                    <i class="bi bi-speedometer2"></i><span>Dashboard</span>
                </a>
            </li>

            <?php if (\App\Helpers\Auth::can('inventory.view')): ?>
            <li class="nav-label">INVENTORY</li>

            <li class="nav-item">
                <a href="<?= APP_URL ?>/inventory/products" class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/inventory/products') ? 'active' : '' ?>">
                    <i class="bi bi-grid-3x3-gap"></i><span>Products</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= APP_URL ?>/inventory/batches" class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/inventory/batches') ? 'active' : '' ?>">
                    <i class="bi bi-box-seam"></i><span>Batches & Stock</span>
                </a>
            </li>
            <?php if (\App\Helpers\Auth::can('inventory.receive')): ?>
            <li class="nav-item">
                <a href="<?= APP_URL ?>/inventory/batches/receive" class="nav-link">
                    <i class="bi bi-box-arrow-in-down"></i><span>Receive Stock</span>
                </a>
            </li>
            <?php endif; ?>
            <?php endif; ?>

            <?php if (\App\Helpers\Auth::can('sales.view')): ?>
            <li class="nav-label">SALES</li>

            <li class="nav-item">
                <a href="<?= APP_URL ?>/sales" class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/sales') && !str_contains($_SERVER['REQUEST_URI'], '/sales/create') ? 'active' : '' ?>">
                    <i class="bi bi-receipt"></i><span>Sales</span>
                </a>
            </li>
            <?php if (\App\Helpers\Auth::can('sales.create')): ?>
            <li class="nav-item">
                <a href="<?= APP_URL ?>/sales/create" class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/sales/create') ? 'active' : '' ?>">
                    <i class="bi bi-cart-plus"></i><span>New Sale (POS)</span>
                </a>
            </li>
            <?php endif; ?>
            <?php endif; ?>

            <?php if (\App\Helpers\Auth::can('customers.view')): ?>
            <li class="nav-item">
                <a href="<?= APP_URL ?>/customers" class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/customers') ? 'active' : '' ?>">
                    <i class="bi bi-people"></i><span>Customers</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if (\App\Helpers\Auth::can('reports.view')): ?>
            <li class="nav-label">ANALYTICS</li>

            <li class="nav-item">
                <a href="<?= APP_URL ?>/reports" class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/reports') ? 'active' : '' ?>">
                    <i class="bi bi-bar-chart-line"></i><span>Reports</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if (\App\Helpers\Auth::can('pnl.view')): ?>
            <li class="nav-item">
                <a href="<?= APP_URL ?>/pnl" class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/pnl') ? 'active' : '' ?>">
                    <i class="bi bi-graph-up-arrow"></i><span>Profit & Loss</span>
                </a>
            </li>
            <?php endif; ?>

            <li class="nav-label">SYSTEM</li>

            <?php if (\App\Helpers\Auth::can('users.view')): ?>
            <li class="nav-item">
                <a href="<?= APP_URL ?>/users" class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/users') ? 'active' : '' ?>">
                    <i class="bi bi-person-gear"></i><span>Users</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if (\App\Helpers\Auth::can('audit.view')): ?>
            <li class="nav-item">
                <a href="<?= APP_URL ?>/audit" class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/audit') ? 'active' : '' ?>">
                    <i class="bi bi-shield-check"></i><span>Audit Log</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if (\App\Helpers\Auth::can('settings.view')): ?>
            <li class="nav-item">
                <a href="<?= APP_URL ?>/settings" class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/settings') ? 'active' : '' ?>">
                    <i class="bi bi-gear"></i><span>Settings</span>
                </a>
            </li>
            <?php endif; ?>

            <li class="nav-item">
                <a href="<?= APP_URL ?>/calculator" class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/calculator') ? 'active' : '' ?>">
                    <i class="bi bi-calculator"></i><span>Calculator</span>
                </a>
            </li>

        </ul>
    </div>

    <!-- Sidebar user -->
    <div class="sidebar-user">
        <div class="d-flex align-items-center gap-2 px-3 py-2">
            <div class="user-avatar"><?= strtoupper(substr($currentUser['full_name'] ?? 'U', 0, 1)) ?></div>
            <div class="flex-grow-1 overflow-hidden">
                <div class="fw-semibold text-truncate small"><?= htmlspecialchars($currentUser['full_name'] ?? '') ?></div>
                <div class="text-muted" style="font-size:11px"><?= htmlspecialchars($currentUser['role_display'] ?? '') ?></div>
            </div>
        </div>
    </div>
</nav>

<!-- ═══ MAIN CONTENT ══════════════════════════════════════════════════ -->
<div class="main-content" id="mainContent">

    <!-- TOPBAR -->
    <header class="topbar">
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-sm btn-outline-secondary d-lg-none" id="sidebarToggle">
                <i class="bi bi-list fs-5"></i>
            </button>
            <button class="btn btn-sm btn-outline-secondary d-none d-lg-inline-flex" id="sidebarCollapseBtn" title="Toggle sidebar">
                <i class="bi bi-layout-sidebar"></i>
            </button>
            <nav aria-label="breadcrumb" class="d-none d-md-block">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard">Home</a></li>
                    <li class="breadcrumb-item active"><?= htmlspecialchars($pageTitle ?? '') ?></li>
                </ol>
            </nav>
        </div>

        <div class="d-flex align-items-center gap-2">
            <!-- Live clock -->
            <span class="text-muted small d-none d-md-inline" id="liveClock"></span>

            <!-- Notifications -->
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-secondary position-relative" id="notifBtn" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-bell"></i>
                    <span class="notif-badge d-none" id="notifBadge">0</span>
                </button>
                <div class="dropdown-menu dropdown-menu-end notif-dropdown p-0" style="width:360px;max-height:480px;overflow-y:auto">
                    <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom">
                        <strong class="small">Notifications</strong>
                        <button class="btn btn-link btn-sm p-0 text-decoration-none small" id="markAllReadBtn">Mark all read</button>
                    </div>
                    <div id="notifList">
                        <div class="text-center text-muted py-4 small">Loading…</div>
                    </div>
                </div>
            </div>

            <!-- User menu -->
            <div class="dropdown">
                <button class="btn btn-sm d-flex align-items-center gap-2 border" data-bs-toggle="dropdown">
                    <div class="user-avatar sm"><?= strtoupper(substr($currentUser['full_name'] ?? 'U', 0, 1)) ?></div>
                    <span class="d-none d-md-inline small"><?= htmlspecialchars($currentUser['full_name'] ?? '') ?></span>
                    <i class="bi bi-chevron-down small"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><h6 class="dropdown-header"><?= htmlspecialchars($currentUser['role_display'] ?? '') ?></h6></li>
                    <li><a class="dropdown-item" href="<?= APP_URL ?>/profile"><i class="bi bi-person me-2"></i>My Profile</a></li>
                    <li><a class="dropdown-item" href="<?= APP_URL ?>/profile/change-password"><i class="bi bi-key me-2"></i>Change Password</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="<?= APP_URL ?>/logout"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                </ul>
            </div>
        </div>
    </header>

    <!-- FLASH MESSAGES -->
    <?php
    $flashTypes = ['success' => 'success', 'error' => 'danger', 'warning' => 'warning', 'info' => 'info'];
    foreach ($flashTypes as $type => $bsClass):
        $msg = \App\Helpers\Session::getFlash($type);
        if ($msg):
    ?>
    <div class="alert alert-<?= $bsClass ?> alert-dismissible fade show m-3 mb-0 shadow-sm" role="alert">
        <i class="bi bi-<?= $bsClass === 'success' ? 'check-circle' : ($bsClass === 'danger' ? 'exclamation-triangle' : 'info-circle') ?> me-2"></i>
        <?= htmlspecialchars($msg) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; endforeach; ?>

    <!-- PAGE CONTENT -->
    <main class="page-content">
        <?= $content ?>
    </main>

    <footer class="page-footer text-muted small">
        &copy; <?= date('Y') ?> <?= APP_NAME ?> v<?= APP_VERSION ?>
        &nbsp;|&nbsp; <?= htmlspecialchars($currentUser['full_name'] ?? '') ?>
        &nbsp;|&nbsp; <span id="footerTime"></span>
    </footer>
</div>

<!-- ═══ CALCULATOR FLOAT BUTTON ═══════════════════════════════════════ -->
<button class="calc-fab" id="calcFab" title="Business Calculator" onclick="window.location='<?= APP_URL ?>/calculator'">
    <i class="bi bi-calculator-fill"></i>
</button>

<!-- ═══ SIDEBAR OVERLAY (mobile) ═════════════════════════════════════ -->
<div class="sidebar-overlay d-lg-none" id="sidebarOverlay"></div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<!-- App JS -->
<script src="<?= APP_URL ?>/js/app.js"></script>

<script>
const APP_URL  = '<?= APP_URL ?>';
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;
</script>

</body>
</html>

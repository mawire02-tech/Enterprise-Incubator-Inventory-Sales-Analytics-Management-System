<?php /* errors/404.php */ ?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>404 – <?= APP_NAME ?></title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="min-height:100vh">
<div class="text-center p-4">
    <i class="bi bi-map text-primary" style="font-size:4rem"></i>
    <h1 class="display-4 fw-bold mt-3">404</h1>
    <p class="text-muted mb-4">Page not found. The page you're looking for doesn't exist or has been moved.</p>
    <a href="<?= APP_URL ?>/dashboard" class="btn btn-primary">
        <i class="bi bi-house me-2"></i>Back to Dashboard
    </a>
</div>
</body></html>

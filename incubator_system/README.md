# Enterprise Incubator Inventory, Sales & Analytics Management System

A production-ready, full-stack PHP MVC web application for managing incubator inventory, sales, customers, and business analytics — built for the Zimbabwean market.

---

## System Requirements

| Component | Minimum Version |
|-----------|----------------|
| PHP       | 8.1+            |
| MySQL     | 8.0+            |
| Apache    | 2.4+ (mod_rewrite) |
| RAM       | 512 MB+         |

**Required PHP Extensions:** `pdo_mysql`, `mbstring`, `json`, `openssl`, `fileinfo`, `session`

---

## Installation

### 1. Clone / Deploy Files

Place the project in your web server's document root. The public entry point is `public/index.php`.

```
/var/www/html/incubator_system/
├── public/        ← Web root (point Apache/Nginx here or use as subdirectory)
├── app/
├── config/
├── database/
├── routes/
└── storage/
```

### 2. Configure Apache Virtual Host

**Option A — Subdirectory** (e.g., `http://localhost/incubator_system/public/`)

Ensure `mod_rewrite` is enabled. The included `.htaccess` handles routing.

**Option B — Domain root** (e.g., `http://yourdomain.com/`)

```apache
<VirtualHost *:80>
    DocumentRoot /var/www/html/incubator_system/public
    ServerName yourdomain.com
    <Directory /var/www/html/incubator_system/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### 3. Set Application URL

Edit `config/config.php` and update `APP_URL`:

```php
define('APP_URL', 'http://localhost/incubator_system/public');
// or for domain root:
define('APP_URL', 'https://yourdomain.com');
```

### 4. Configure Database

Edit `config/config.php`:

```php
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'incubator_system');
define('DB_USER', 'your_db_user');
define('DB_PASS', 'your_db_password');
```

### 5. Run Setup Script

```bash
cd /path/to/incubator_system
php setup.php
```

This will:
- Create the database and all tables
- Insert roles, permissions and seed data
- Create demo users with properly hashed passwords
- Create storage directories

### 6. Set Directory Permissions

```bash
chmod -R 755 storage/
chmod -R 755 public/
```

### 7. Access the System

Open your browser: `http://localhost/incubator_system/public`

---

## Default Login Credentials

| Role          | Username  | Password      |
|---------------|-----------|---------------|
| Administrator | admin     | Admin@1234    |
| Manager       | manager1  | Password@123  |
| Sales Officer | sales1    | Password@123  |
| Stock Clerk   | clerk1    | Password@123  |

> ⚠️ **Change all passwords immediately after first login.**

---

## Directory Structure

```
incubator_system/
├── app/
│   ├── Controllers/          # Request handlers
│   │   ├── AuthController.php
│   │   ├── DashboardController.php
│   │   ├── InventoryController.php
│   │   ├── SalesController.php
│   │   ├── CustomerController.php
│   │   ├── UserController.php
│   │   ├── ReportsController.php
│   │   ├── MiscControllers.php   # Audit, Notifications, PnL, Calculator
│   │   └── SettingsController.php
│   ├── Core/
│   │   ├── Controller.php     # Base controller
│   │   ├── Database.php       # PDO singleton
│   │   ├── Logger.php         # File logger
│   │   ├── Model.php          # Base model
│   │   └── Router.php         # URL dispatcher
│   ├── Helpers/
│   │   ├── Auth.php           # Authentication + RBAC/PBAC
│   │   └── Helpers.php        # Session, CSRF, Validator
│   ├── Middleware/
│   │   ├── AuthMiddleware.php
│   │   └── GuestMiddleware.php
│   ├── Services/
│   │   ├── AuditService.php
│   │   ├── BackupService.php
│   │   ├── ExportService.php
│   │   ├── InventoryService.php
│   │   ├── LedgerService.php     # Also contains ProfitLossService
│   │   ├── NotificationService.php
│   │   └── SalesService.php
│   └── Views/
│       ├── layouts/main.php      # Master layout
│       ├── auth/                 # Login, profile, password
│       ├── dashboard/
│       ├── inventory/
│       ├── sales/                # POS, invoice, returns
│       ├── customers/
│       ├── users/
│       ├── reports/
│       ├── audit/
│       ├── notifications/
│       ├── calculator/
│       ├── settings/
│       └── errors/
├── config/
│   └── config.php
├── database/
│   ├── migrations/
│   │   └── 001_schema.sql        # Full 18-table schema
│   └── seeds/
│       └── 001_seed_data.sql     # Roles, permissions, demo data
├── public/
│   ├── index.php                 # Entry point
│   ├── .htaccess
│   ├── manifest.json             # PWA manifest
│   ├── css/app.css
│   └── js/app.js
├── routes/
│   └── web.php
├── storage/
│   ├── logs/
│   ├── backups/
│   ├── exports/
│   └── uploads/
└── setup.php
```

---

## Module Overview

### Authentication & Security
- Argon2id password hashing
- CSRF protection on all POST forms
- Session regeneration on login
- Idle session timeout (30 minutes default)
- Account lockout after 5 failed attempts (30-minute lockout)
- XSS prevention via `htmlspecialchars()` everywhere
- SQL injection prevention via PDO prepared statements
- Secure cookie flags (HttpOnly, SameSite=Strict)

### RBAC + PBAC
Four built-in roles with 40+ permissions across 9 modules. Administrators can grant or deny individual permissions per user on top of their role's defaults.

### Inventory Module
- Product catalog with SKU, category, capacity, threshold
- Batch-based stock tracking with FIFO deduction
- Receive stock → auto batch code generation
- Stock adjustments: add, remove, damage, correction
- Full movement history per batch
- Auto low-stock and out-of-stock alerts
- Batch depletion detection and archiving with P&L summary

### Sales Module (POS)
- Product grid with live stock display
- FIFO batch selection (automatic)
- Dynamic cart with quantity and price editing
- Discounts: percentage or fixed amount
- Tax support
- Payment methods: Cash, EcoCash, OneMoney, Bank Transfer, Card, Credit
- Invoice generation with printable receipt
- Sale returns with optional restock
- Sale voiding with audit trail

### Daily Ledger
Automatically maintained for every sale/return. Tracks: transactions, units sold, revenue, COGS, gross profit, returns, net profit, discounts, tax, and revenue by payment method.

### Profit & Loss
- Period P&L statements (COGS, gross profit, net profit)
- Gross and net margin percentages
- Batch profitability analysis
- Product margin comparison
- Monthly revenue/profit trend charts
- Inventory valuation at cost

### Reports
- Sales report grouped by day/week/month
- Inventory report with stock levels and valuation
- P&L statement with trend charts
- Daily ledger with period totals
- CSV export for all report types

### Real-Time Features
- Server-Sent Events (SSE) for live dashboard KPI updates
- Notification badge polling every 30 seconds
- AJAX-based POS cart (no page reloads)
- Live activity feed on dashboard

### Audit Trail
Every significant action is logged: user, action, module, description, old/new values, IP address, device type, session ID and timestamp.

### Business Calculator
Six modes: Profit Margin, Markup, Discount, Break-Even, Inventory Valuation, Revenue Forecast — accessible via floating button from any page.

---

## Environment Variables (Optional)

You can override `config.php` settings using environment variables:

```bash
export APP_ENV=production
export APP_URL=https://yourdomain.com
export DB_HOST=127.0.0.1
export DB_NAME=incubator_system
export DB_USER=dbuser
export DB_PASS=dbpassword
export APP_KEY=your-32-char-secret-key-here!!
```

---

## Production Checklist

- [ ] Change `APP_ENV` to `production` in `config/config.php`
- [ ] Set `APP_DEBUG` to `false`
- [ ] Update `APP_KEY` to a strong random 32-character string
- [ ] Change all default passwords
- [ ] Configure SMTP in Settings for email alerts
- [ ] Enable and test backups
- [ ] Set up a cron job for scheduled backups:
  ```bash
  0 2 * * * php /path/to/incubator_system/setup.php backup >> /dev/null 2>&1
  ```
- [ ] Restrict database user to minimum required privileges
- [ ] Set up HTTPS (SSL certificate)
- [ ] Set `storage/` permissions to `755`, not world-writable

---

## Technology Stack

| Layer      | Technology                      |
|------------|---------------------------------|
| Backend    | PHP 8.1+ (custom MVC, no framework) |
| Database   | MySQL 8.0+ (PDO, prepared statements) |
| Frontend   | Bootstrap 5.3, Bootstrap Icons  |
| Charts     | Chart.js 4.4                    |
| Real-time  | Server-Sent Events (SSE), AJAX  |
| Security   | Argon2id, CSRF tokens, RBAC+PBAC |
| PWA        | Web App Manifest                |

---

## License

Proprietary — Enterprise Incubator Systems. All rights reserved.

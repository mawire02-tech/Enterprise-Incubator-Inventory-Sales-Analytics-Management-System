-- ============================================================
-- Enterprise Incubator Inventory, Sales & Analytics System
-- Database Schema v1.0
-- MySQL 8.0+
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- ============================================================
-- DATABASE
-- ============================================================
CREATE DATABASE IF NOT EXISTS incubator_system
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE incubator_system;

-- ============================================================
-- ROLES
-- ============================================================
CREATE TABLE roles (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(50) NOT NULL UNIQUE,
    display_name VARCHAR(100) NOT NULL,
    description TEXT,
    is_system   TINYINT(1) NOT NULL DEFAULT 0,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- PERMISSIONS
-- ============================================================
CREATE TABLE permissions (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    module      VARCHAR(50) NOT NULL,
    action      VARCHAR(50) NOT NULL,
    name        VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_module (module),
    INDEX idx_action (action)
) ENGINE=InnoDB;

-- ============================================================
-- ROLE PERMISSIONS (RBAC + PBAC junction)
-- ============================================================
CREATE TABLE role_permissions (
    role_id       INT UNSIGNED NOT NULL,
    permission_id INT UNSIGNED NOT NULL,
    granted_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    granted_by    INT UNSIGNED,
    PRIMARY KEY (role_id, permission_id),
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- USERS
-- ============================================================
CREATE TABLE users (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_id             INT UNSIGNED NOT NULL,
    username            VARCHAR(50)  NOT NULL UNIQUE,
    email               VARCHAR(150) NOT NULL UNIQUE,
    password_hash       VARCHAR(255) NOT NULL,
    full_name           VARCHAR(150) NOT NULL,
    phone               VARCHAR(20),
    avatar              VARCHAR(255),
    is_active           TINYINT(1) NOT NULL DEFAULT 1,
    is_locked           TINYINT(1) NOT NULL DEFAULT 0,
    failed_attempts     TINYINT UNSIGNED NOT NULL DEFAULT 0,
    locked_until        DATETIME,
    last_login_at       DATETIME,
    last_login_ip       VARCHAR(45),
    password_changed_at DATETIME,
    must_change_password TINYINT(1) NOT NULL DEFAULT 0,
    created_by          INT UNSIGNED,
    created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at          DATETIME,
    FOREIGN KEY (role_id) REFERENCES roles(id),
    INDEX idx_email (email),
    INDEX idx_username (username),
    INDEX idx_role (role_id),
    INDEX idx_active (is_active)
) ENGINE=InnoDB;

-- ============================================================
-- USER PERMISSIONS (override/extra permissions per user)
-- ============================================================
CREATE TABLE user_permissions (
    user_id       INT UNSIGNED NOT NULL,
    permission_id INT UNSIGNED NOT NULL,
    granted       TINYINT(1) NOT NULL DEFAULT 1,
    granted_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    granted_by    INT UNSIGNED,
    PRIMARY KEY (user_id, permission_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- SESSIONS
-- ============================================================
CREATE TABLE user_sessions (
    id           VARCHAR(128) PRIMARY KEY,
    user_id      INT UNSIGNED NOT NULL,
    ip_address   VARCHAR(45),
    user_agent   TEXT,
    device_type  VARCHAR(50),
    payload      TEXT,
    last_active  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    expires_at   DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB;

-- ============================================================
-- CUSTOMERS
-- ============================================================
CREATE TABLE customers (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_code VARCHAR(20) NOT NULL UNIQUE,
    full_name     VARCHAR(150) NOT NULL,
    email         VARCHAR(150),
    phone         VARCHAR(20),
    address       TEXT,
    city          VARCHAR(100),
    country       VARCHAR(100) DEFAULT 'Zimbabwe',
    notes         TEXT,
    is_active     TINYINT(1) NOT NULL DEFAULT 1,
    created_by    INT UNSIGNED,
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at    DATETIME,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_code (customer_code),
    INDEX idx_name (full_name),
    INDEX idx_phone (phone),
    FULLTEXT idx_search (full_name, email, phone)
) ENGINE=InnoDB;

-- ============================================================
-- INCUBATOR PRODUCTS (product catalog)
-- ============================================================
CREATE TABLE products (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sku             VARCHAR(50) NOT NULL UNIQUE,
    name            VARCHAR(200) NOT NULL,
    description     TEXT,
    category        VARCHAR(100),
    capacity        INT UNSIGNED COMMENT 'Egg capacity',
    brand           VARCHAR(100),
    model           VARCHAR(100),
    unit            VARCHAR(20) NOT NULL DEFAULT 'unit',
    low_stock_threshold INT UNSIGNED NOT NULL DEFAULT 5,
    is_active       TINYINT(1) NOT NULL DEFAULT 1,
    image_path      VARCHAR(255),
    created_by      INT UNSIGNED,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_sku (sku),
    FULLTEXT idx_search (name, description, brand, model)
) ENGINE=InnoDB;

-- ============================================================
-- INVENTORY BATCHES
-- ============================================================
CREATE TABLE inventory_batches (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id       INT UNSIGNED NOT NULL,
    batch_code       VARCHAR(50) NOT NULL UNIQUE,
    supplier_name    VARCHAR(150),
    supplier_ref     VARCHAR(100),
    quantity_received INT UNSIGNED NOT NULL DEFAULT 0,
    quantity_current  INT UNSIGNED NOT NULL DEFAULT 0,
    quantity_sold     INT UNSIGNED NOT NULL DEFAULT 0,
    quantity_damaged  INT UNSIGNED NOT NULL DEFAULT 0,
    quantity_adjusted INT NOT NULL DEFAULT 0 COMMENT 'Positive=added, Negative=removed',
    cost_price       DECIMAL(12,2) NOT NULL,
    selling_price    DECIMAL(12,2) NOT NULL,
    acquisition_date DATE NOT NULL,
    expiry_date      DATE,
    notes            TEXT,
    status           ENUM('active','depleted','archived') NOT NULL DEFAULT 'active',
    total_revenue    DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    total_cost       DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    gross_profit     DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    net_profit       DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    loss_amount      DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    depleted_at      DATETIME,
    archived_at      DATETIME,
    archived_by      INT UNSIGNED,
    created_by       INT UNSIGNED,
    created_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (archived_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_product (product_id),
    INDEX idx_status (status),
    INDEX idx_batch_code (batch_code),
    INDEX idx_acquisition (acquisition_date)
) ENGINE=InnoDB;

-- ============================================================
-- INVENTORY MOVEMENTS (every stock change logged)
-- ============================================================
CREATE TABLE inventory_movements (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    batch_id        INT UNSIGNED NOT NULL,
    product_id      INT UNSIGNED NOT NULL,
    movement_type   ENUM('receive','sale','return','adjustment','damage','transfer_in','transfer_out') NOT NULL,
    reference_type  VARCHAR(50)  COMMENT 'sale, return, adjustment, etc.',
    reference_id    INT UNSIGNED,
    quantity_before INT NOT NULL,
    quantity_change INT NOT NULL COMMENT 'Positive=in, Negative=out',
    quantity_after  INT NOT NULL,
    unit_cost       DECIMAL(12,2),
    unit_price      DECIMAL(12,2),
    notes           TEXT,
    performed_by    INT UNSIGNED,
    performed_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (batch_id) REFERENCES inventory_batches(id),
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (performed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_batch (batch_id),
    INDEX idx_product (product_id),
    INDEX idx_type (movement_type),
    INDEX idx_performed_at (performed_at),
    INDEX idx_reference (reference_type, reference_id)
) ENGINE=InnoDB;

-- ============================================================
-- SALES (invoice header)
-- ============================================================
CREATE TABLE sales (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    invoice_number  VARCHAR(30) NOT NULL UNIQUE,
    customer_id     INT UNSIGNED,
    customer_name   VARCHAR(150) COMMENT 'Snapshot for walk-in customers',
    customer_phone  VARCHAR(20),
    sale_date       DATE NOT NULL,
    subtotal        DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    discount_type   ENUM('none','percent','fixed') NOT NULL DEFAULT 'none',
    discount_value  DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    discount_amount DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    tax_rate        DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    tax_amount      DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    total_amount    DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    total_cost      DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    gross_profit    DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    payment_method  ENUM('cash','ecocash','onemoney','bank_transfer','card','credit') NOT NULL DEFAULT 'cash',
    payment_status  ENUM('paid','partial','unpaid','refunded') NOT NULL DEFAULT 'paid',
    amount_paid     DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    amount_due      DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    status          ENUM('completed','returned','voided') NOT NULL DEFAULT 'completed',
    notes           TEXT,
    served_by       INT UNSIGNED,
    created_by      INT UNSIGNED,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
    FOREIGN KEY (served_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_invoice (invoice_number),
    INDEX idx_customer (customer_id),
    INDEX idx_sale_date (sale_date),
    INDEX idx_status (status),
    INDEX idx_payment_status (payment_status)
) ENGINE=InnoDB;

-- ============================================================
-- SALE ITEMS (invoice line items)
-- ============================================================
CREATE TABLE sale_items (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sale_id         INT UNSIGNED NOT NULL,
    product_id      INT UNSIGNED NOT NULL,
    batch_id        INT UNSIGNED NOT NULL,
    quantity        INT UNSIGNED NOT NULL,
    unit_cost       DECIMAL(12,2) NOT NULL,
    unit_price      DECIMAL(12,2) NOT NULL,
    discount_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    line_total      DECIMAL(14,2) NOT NULL,
    line_cost       DECIMAL(14,2) NOT NULL,
    line_profit     DECIMAL(14,2) NOT NULL,
    product_snapshot JSON COMMENT 'Snapshot of product at time of sale',
    FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (batch_id) REFERENCES inventory_batches(id),
    INDEX idx_sale (sale_id),
    INDEX idx_product (product_id),
    INDEX idx_batch (batch_id)
) ENGINE=InnoDB;

-- ============================================================
-- SALE RETURNS
-- ============================================================
CREATE TABLE sale_returns (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    return_number   VARCHAR(30) NOT NULL UNIQUE,
    sale_id         INT UNSIGNED NOT NULL,
    return_date     DATE NOT NULL,
    reason          TEXT NOT NULL,
    return_amount   DECIMAL(14,2) NOT NULL,
    restock         TINYINT(1) NOT NULL DEFAULT 1,
    status          ENUM('pending','approved','rejected') NOT NULL DEFAULT 'approved',
    notes           TEXT,
    processed_by    INT UNSIGNED,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sale_id) REFERENCES sales(id),
    FOREIGN KEY (processed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_sale (sale_id)
) ENGINE=InnoDB;

CREATE TABLE return_items (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    return_id       INT UNSIGNED NOT NULL,
    sale_item_id    INT UNSIGNED NOT NULL,
    product_id      INT UNSIGNED NOT NULL,
    batch_id        INT UNSIGNED NOT NULL,
    quantity        INT UNSIGNED NOT NULL,
    unit_price      DECIMAL(12,2) NOT NULL,
    line_total      DECIMAL(14,2) NOT NULL,
    FOREIGN KEY (return_id) REFERENCES sale_returns(id) ON DELETE CASCADE,
    FOREIGN KEY (sale_item_id) REFERENCES sale_items(id),
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (batch_id) REFERENCES inventory_batches(id)
) ENGINE=InnoDB;

-- ============================================================
-- STOCK ADJUSTMENTS
-- ============================================================
CREATE TABLE stock_adjustments (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    adjustment_code VARCHAR(30) NOT NULL UNIQUE,
    batch_id        INT UNSIGNED NOT NULL,
    product_id      INT UNSIGNED NOT NULL,
    adjustment_type ENUM('add','remove','damage','correction') NOT NULL,
    quantity_before INT NOT NULL,
    quantity_change INT NOT NULL,
    quantity_after  INT NOT NULL,
    reason          TEXT NOT NULL,
    notes           TEXT,
    adjusted_by     INT UNSIGNED,
    adjusted_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (batch_id) REFERENCES inventory_batches(id),
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (adjusted_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_batch (batch_id),
    INDEX idx_adjusted_at (adjusted_at)
) ENGINE=InnoDB;

-- ============================================================
-- DAILY SALES LEDGER
-- ============================================================
CREATE TABLE daily_ledger (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ledger_date         DATE NOT NULL UNIQUE,
    total_transactions  INT UNSIGNED NOT NULL DEFAULT 0,
    total_units_sold    INT UNSIGNED NOT NULL DEFAULT 0,
    total_revenue       DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    total_inventory_cost DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    gross_profit        DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    total_returns       DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    net_profit          DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    total_discounts     DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    total_tax           DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    cash_sales          DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    ecocash_sales       DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    other_sales         DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    opening_stock_value DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    closing_stock_value DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    notes               TEXT,
    finalized           TINYINT(1) NOT NULL DEFAULT 0,
    finalized_by        INT UNSIGNED,
    finalized_at        DATETIME,
    created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_date (ledger_date)
) ENGINE=InnoDB;

-- ============================================================
-- NOTIFICATIONS
-- ============================================================
CREATE TABLE notifications (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    type            VARCHAR(50) NOT NULL,
    title           VARCHAR(200) NOT NULL,
    message         TEXT NOT NULL,
    severity        ENUM('info','success','warning','danger') NOT NULL DEFAULT 'info',
    icon            VARCHAR(50),
    reference_type  VARCHAR(50),
    reference_id    INT UNSIGNED,
    target_role     INT UNSIGNED COMMENT 'NULL = all roles',
    target_user     INT UNSIGNED COMMENT 'NULL = broadcast to role',
    is_read         TINYINT(1) NOT NULL DEFAULT 0,
    read_at         DATETIME,
    read_by         INT UNSIGNED,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    expires_at      DATETIME,
    FOREIGN KEY (target_role) REFERENCES roles(id) ON DELETE SET NULL,
    FOREIGN KEY (target_user) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_target_user (target_user),
    INDEX idx_target_role (target_role),
    INDEX idx_type (type),
    INDEX idx_created (created_at),
    INDEX idx_read (is_read)
) ENGINE=InnoDB;

-- ============================================================
-- AUDIT LOG
-- ============================================================
CREATE TABLE audit_logs (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED,
    username        VARCHAR(50) COMMENT 'Snapshot',
    action          VARCHAR(100) NOT NULL,
    module          VARCHAR(50) NOT NULL,
    description     TEXT NOT NULL,
    old_values      JSON,
    new_values      JSON,
    ip_address      VARCHAR(45),
    user_agent      TEXT,
    device_type     VARCHAR(50),
    session_id      VARCHAR(128),
    reference_type  VARCHAR(50),
    reference_id    INT UNSIGNED,
    severity        ENUM('low','medium','high','critical') NOT NULL DEFAULT 'low',
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_action (action),
    INDEX idx_module (module),
    INDEX idx_created (created_at),
    INDEX idx_severity (severity),
    INDEX idx_reference (reference_type, reference_id)
) ENGINE=InnoDB;

-- ============================================================
-- REPORT CACHE / GENERATED REPORTS
-- ============================================================
CREATE TABLE generated_reports (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    report_type     VARCHAR(50) NOT NULL,
    report_name     VARCHAR(200) NOT NULL,
    parameters      JSON,
    file_path       VARCHAR(255),
    file_format     ENUM('pdf','excel','csv') NOT NULL,
    file_size       INT UNSIGNED,
    status          ENUM('generating','ready','failed') NOT NULL DEFAULT 'generating',
    generated_by    INT UNSIGNED,
    generated_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    expires_at      DATETIME,
    download_count  INT UNSIGNED NOT NULL DEFAULT 0,
    FOREIGN KEY (generated_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_type (report_type),
    INDEX idx_generated_at (generated_at)
) ENGINE=InnoDB;

-- ============================================================
-- SYSTEM SETTINGS
-- ============================================================
CREATE TABLE system_settings (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    data_type   ENUM('string','integer','decimal','boolean','json') NOT NULL DEFAULT 'string',
    group_name  VARCHAR(50),
    description TEXT,
    updated_by  INT UNSIGNED,
    updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- BACKUP LOG
-- ============================================================
CREATE TABLE backup_logs (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    backup_type ENUM('manual','scheduled','auto') NOT NULL,
    file_name   VARCHAR(255) NOT NULL,
    file_path   VARCHAR(500) NOT NULL,
    file_size   BIGINT UNSIGNED,
    status      ENUM('success','failed','in_progress') NOT NULL,
    notes       TEXT,
    triggered_by INT UNSIGNED,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (triggered_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- Seed Data v1.0
-- ============================================================

USE incubator_system;

-- ============================================================
-- ROLES
-- ============================================================
INSERT INTO roles (name, display_name, description, is_system) VALUES
('administrator', 'Administrator', 'Full system access with all permissions', 1),
('manager',       'Manager',       'Manage inventory, sales, reports and users', 1),
('sales_officer', 'Sales Officer', 'Process sales, view inventory and customers', 1),
('stock_clerk',   'Stock Clerk',   'Manage inventory stock and movements', 1);

-- ============================================================
-- PERMISSIONS
-- ============================================================
INSERT INTO permissions (module, action, name, description) VALUES
-- Dashboard
('dashboard', 'view',            'dashboard.view',           'View dashboard'),
-- Users
('users',     'view',            'users.view',               'View user list'),
('users',     'create',          'users.create',             'Create new users'),
('users',     'edit',            'users.edit',               'Edit user details'),
('users',     'delete',          'users.delete',             'Delete users'),
('users',     'change_role',     'users.change_role',        'Change user roles'),
('users',     'unlock',          'users.unlock',             'Unlock locked accounts'),
-- Inventory
('inventory', 'view',            'inventory.view',           'View inventory'),
('inventory', 'receive',         'inventory.receive',        'Receive new stock'),
('inventory', 'edit',            'inventory.edit',           'Edit inventory details'),
('inventory', 'adjust',          'inventory.adjust',         'Adjust stock quantities'),
('inventory', 'delete',          'inventory.delete',         'Delete inventory records'),
('inventory', 'view_cost',       'inventory.view_cost',      'View cost prices'),
-- Products
('products',  'view',            'products.view',            'View products'),
('products',  'create',          'products.create',          'Create products'),
('products',  'edit',            'products.edit',            'Edit products'),
('products',  'delete',          'products.delete',          'Delete products'),
-- Sales
('sales',     'view',            'sales.view',               'View sales records'),
('sales',     'create',          'sales.create',             'Process new sales'),
('sales',     'edit',            'sales.edit',               'Edit sale records'),
('sales',     'void',            'sales.void',               'Void sales'),
('sales',     'return',          'sales.return',             'Process returns'),
('sales',     'view_profit',     'sales.view_profit',        'View profit data on sales'),
-- Customers
('customers', 'view',            'customers.view',           'View customers'),
('customers', 'create',          'customers.create',         'Create customers'),
('customers', 'edit',            'customers.edit',           'Edit customer details'),
('customers', 'delete',          'customers.delete',         'Delete customers'),
-- Reports
('reports',   'view',            'reports.view',             'View reports'),
('reports',   'generate',        'reports.generate',         'Generate reports'),
('reports',   'export',          'reports.export',           'Export reports'),
('reports',   'financial',       'reports.financial',        'View financial reports'),
-- Profit & Loss
('pnl',       'view',            'pnl.view',                 'View P&L reports'),
-- Audit
('audit',     'view',            'audit.view',               'View audit logs'),
('audit',     'export',          'audit.export',             'Export audit logs'),
-- Notifications
('notifications','manage',       'notifications.manage',     'Manage notifications'),
-- Settings
('settings',  'view',            'settings.view',            'View settings'),
('settings',  'edit',            'settings.edit',            'Edit settings'),
('settings',  'backup',          'settings.backup',          'Manage backups');

-- ============================================================
-- ROLE PERMISSIONS
-- Administrator: ALL permissions
-- ============================================================
INSERT INTO role_permissions (role_id, permission_id)
SELECT 1, id FROM permissions;

-- ============================================================
-- Manager: view sales only (no creating/voiding/returning),
--          no users management, no audit logs, no settings
-- ============================================================
INSERT INTO role_permissions (role_id, permission_id)
SELECT 2, id FROM permissions
WHERE name IN (
    'dashboard.view',
    'inventory.view','inventory.receive','inventory.edit','inventory.adjust','inventory.view_cost',
    'inventory.delete',
    'products.view','products.create','products.edit','products.delete',
    'sales.view',
    'customers.view','customers.create','customers.edit','customers.delete',
    'reports.view','reports.generate','reports.export','reports.financial',
    'pnl.view',
    'notifications.manage'
);

-- ============================================================
-- Sales Officer: process sales, view inventory/customers.
--               NO access to reports page at all.
--               NO profit/financial data.
-- ============================================================
INSERT INTO role_permissions (role_id, permission_id)
SELECT 3, id FROM permissions
WHERE name IN (
    'dashboard.view',
    'inventory.view',
    'products.view',
    'sales.view','sales.create','sales.return',
    'customers.view','customers.create','customers.edit'
    -- reports.view intentionally omitted: Sales cannot access reports
);

-- ============================================================
-- Stock Clerk: inventory management only.
--              NO reports. Dashboard shows stock alerts &
--              product quantities only (enforced in view).
-- ============================================================
INSERT INTO role_permissions (role_id, permission_id)
SELECT 4, id FROM permissions
WHERE name IN (
    'dashboard.view',
    'inventory.view','inventory.receive','inventory.adjust',
    'products.view'
    -- reports.view intentionally omitted: Stock Clerk cannot access reports
);

-- ============================================================
-- DEFAULT ADMIN USER
-- Password: Admin@1234 (Argon2id hash - regenerate in production)
-- ============================================================
INSERT INTO users (role_id, username, email, password_hash, full_name, phone, is_active, password_changed_at, created_at) VALUES
(1, 'admin', 'admin@incubator.local',
 '$argon2id$v=19$m=65536,t=4,p=1$c29tZXNhbHQ$RdescudvJCsgt3ub+b+dWRWJTmaasfRiu6/1I8JjdPs',
 'System Administrator', '+263771000000', 1, NOW(), NOW());

-- Demo users (password: Password@123)
INSERT INTO users (role_id, username, email, password_hash, full_name, phone, is_active, created_by) VALUES
(2, 'manager1',   'manager@incubator.local',
 '$argon2id$v=19$m=65536,t=4,p=1$c29tZXNhbHQ$RdescudvJCsgt3ub+b+dWRWJTmaasfRiu6/1I8JjdPs',
 'John Moyo', '+263772000001', 1, 1),
(3, 'sales1',     'sales@incubator.local',
 '$argon2id$v=19$m=65536,t=4,p=1$c29tZXNhbHQ$RdescudvJCsgt3ub+b+dWRWJTmaasfRiu6/1I8JjdPs',
 'Tendai Chikwanda', '+263773000002', 1, 1),
(4, 'clerk1',     'clerk@incubator.local',
 '$argon2id$v=19$m=65536,t=4,p=1$c29tZXNhbHQ$RdescudvJCsgt3ub+b+dWRWJTmaasfRiu6/1I8JjdPs',
 'Rudo Mupfudza', '+263774000003', 1, 1);

-- ============================================================
-- PRODUCTS (Incubator Models)
-- ============================================================
INSERT INTO products (sku, name, description, category, capacity, brand, model, unit, low_stock_threshold, is_active, created_by) VALUES
('INC-48',   '48-Egg Incubator',       'Fully automatic 48-egg incubator with digital temperature control and automatic egg turning', 'Automatic', 48,  'HatchPro', 'HP-48A',  'unit', 5, 1, 1),
('INC-56',   '56-Egg Incubator',       'Semi-automatic 56-egg incubator with humidity control', 'Semi-Auto', 56,  'HatchPro', 'HP-56S',  'unit', 3, 1, 1),
('INC-96',   '96-Egg Incubator',       'Commercial 96-egg incubator with LED candler and dual power supply', 'Commercial', 96,  'FarmTech', 'FT-96C',  'unit', 3, 1, 1),
('INC-128',  '128-Egg Incubator',      'Large capacity 128-egg automatic incubator for small farms', 'Commercial', 128, 'FarmTech', 'FT-128A', 'unit', 2, 1, 1),
('INC-176',  '176-Egg Incubator',      'High-capacity automatic incubator with solar backup compatibility', 'Industrial', 176, 'AgroPro',  'AP-176S', 'unit', 2, 1, 1),
('INC-264',  '264-Egg Incubator',      'Industrial-grade 264-egg incubator with external humidity sensor', 'Industrial', 264, 'AgroPro',  'AP-264I', 'unit', 1, 1, 1),
('INC-500',  '500-Egg Incubator',      'Commercial 500-egg incubator for poultry farms', 'Industrial', 500, 'ProHatch', 'PH-500C', 'unit', 1, 1, 1),
('ACC-THERM','Digital Thermometer',    'High precision digital thermometer with hygrometer for incubator monitoring', 'Accessory', NULL, 'HatchPro', 'ACC-TH1', 'unit', 10, 1, 1),
('ACC-CNDL', 'Egg Candler',            'LED egg candler for fertility checking', 'Accessory', NULL, 'FarmTech', 'ACC-CL1', 'unit', 10, 1, 1),
('ACC-TRNY', 'Automatic Turner Tray',  'Replacement automatic egg turner tray, compatible with most models', 'Accessory', NULL, 'HatchPro', 'ACC-TT1', 'unit', 5,  1, 1);

-- ============================================================
-- SAMPLE CUSTOMERS
-- ============================================================
INSERT INTO customers (customer_code, full_name, email, phone, address, city, created_by) VALUES
('CUST-0001', 'Blessing Chirwa',   'blessing.chirwa@email.com',   '+263771100001', '12 Harare Drive',    'Harare',   1),
('CUST-0002', 'Takudzwa Mhembere', 'taku.m@gmail.com',            '+263772200002', '45 Bulawayo Rd',     'Bulawayo', 1),
('CUST-0003', 'Nyasha Farm Ltd',   'nyashafarm@business.co.zw',   '+263773300003', 'Plot 23 Mazowe',     'Mazowe',   1),
('CUST-0004', 'Farai Muradzikwa',  'farai.m@yahoo.com',           '+263774400004', '8 Mutare Ave',       'Mutare',   1),
('CUST-0005', 'Sunshine Poultry',  'info@sunshinepoultry.co.zw',  '+263775500005', '100 Gweru Industrial','Gweru',   1);

-- ============================================================
-- SYSTEM SETTINGS
-- ============================================================
INSERT INTO system_settings (setting_key, setting_value, data_type, group_name, description) VALUES
('company_name',          'Enterprise Incubator Systems',  'string',  'company',    'Company name'),
('company_address',       'Harare, Zimbabwe',              'string',  'company',    'Company address'),
('company_phone',         '+263771000000',                 'string',  'company',    'Company phone'),
('company_email',         'info@incubator.local',          'string',  'company',    'Company email'),
('currency_code',         'USD',                           'string',  'finance',    'Currency code'),
('currency_symbol',       '$',                             'string',  'finance',    'Currency symbol'),
('tax_rate',              '0.00',                          'decimal', 'finance',    'Default tax rate (%)'),
('invoice_prefix',        'INV',                           'string',  'sales',      'Invoice number prefix'),
('return_prefix',         'RET',                           'string',  'sales',      'Return number prefix'),
('batch_prefix',          'BATCH',                         'string',  'inventory',  'Batch code prefix'),
('adjustment_prefix',     'ADJ',                           'string',  'inventory',  'Adjustment code prefix'),
('low_stock_alert',       '5',                             'integer', 'inventory',  'Global low stock threshold'),
('session_timeout',       '1800',                          'integer', 'security',   'Session timeout in seconds'),
('max_login_attempts',    '5',                             'integer', 'security',   'Max failed login attempts before lockout'),
('lockout_duration',      '30',                            'integer', 'security',   'Account lockout duration in minutes'),
('backup_enabled',        '1',                             'boolean', 'backup',     'Enable automatic backups'),
('backup_frequency',      'daily',                         'string',  'backup',     'Backup frequency'),
('backup_retention',      '30',                            'integer', 'backup',     'Backup retention in days'),
('email_alerts',          '0',                             'boolean', 'notifications','Enable email alerts'),
('smtp_host',             '',                              'string',  'email',      'SMTP host'),
('smtp_port',             '587',                           'integer', 'email',      'SMTP port'),
('smtp_user',             '',                              'string',  'email',      'SMTP username'),
('smtp_pass',             '',                              'string',  'email',      'SMTP password (encrypted)'),
('items_per_page',        '25',                            'integer', 'ui',         'Default pagination size'),
('date_format',           'd/m/Y',                         'string',  'ui',         'Date display format'),
('datetime_format',       'd/m/Y H:i',                     'string',  'ui',         'DateTime display format');

-- Enterprise Incubator System Backup
-- Generated: 2026-06-12 07:30:47
-- Database: incubator_system

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `audit_logs`;
CREATE TABLE `audit_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL COMMENT 'Snapshot',
  `action` varchar(100) NOT NULL,
  `module` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `device_type` varchar(50) DEFAULT NULL,
  `session_id` varchar(128) DEFAULT NULL,
  `reference_type` varchar(50) DEFAULT NULL,
  `reference_id` int(10) unsigned DEFAULT NULL,
  `severity` enum('low','medium','high','critical') NOT NULL DEFAULT 'low',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_action` (`action`),
  KEY `idx_module` (`module`),
  KEY `idx_created` (`created_at`),
  KEY `idx_severity` (`severity`),
  KEY `idx_reference` (`reference_type`,`reference_id`),
  CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `audit_logs` (`id`, `user_id`, `username`, `action`, `module`, `description`, `old_values`, `new_values`, `ip_address`, `user_agent`, `device_type`, `session_id`, `reference_type`, `reference_id`, `severity`, `created_at`) VALUES
('1', '2', 'manager1', 'logout', 'auth', 'User \'manager1\' logged out.', NULL, NULL, '::1', NULL, NULL, NULL, NULL, NULL, 'low', '2026-06-10 14:07:40'),
('2', '3', 'sales1', 'login', 'auth', 'User \'sales1\' logged in.', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'desktop', NULL, NULL, NULL, 'low', '2026-06-10 14:08:26'),
('3', '3', 'sales1', 'password_changed', 'auth', 'User changed their password.', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'desktop', 'u97kbpl9pq0rkfunnam6245qvq', 'user', '3', 'medium', '2026-06-10 14:10:17'),
('4', '3', 'sales1', 'logout', 'auth', 'User \'sales1\' logged out.', NULL, NULL, '::1', NULL, NULL, NULL, NULL, NULL, 'low', '2026-06-10 14:10:33'),
('5', NULL, 'admin', 'login_failed', 'auth', 'Failed login attempt for \'admin\'.', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'desktop', NULL, NULL, NULL, 'medium', '2026-06-10 14:10:37'),
('6', NULL, 'admin', 'login_failed', 'auth', 'Failed login attempt for \'admin\'.', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'desktop', NULL, NULL, NULL, 'medium', '2026-06-10 14:11:07'),
('7', NULL, 'admin', 'login_failed', 'auth', 'Failed login attempt for \'admin\'.', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'desktop', NULL, NULL, NULL, 'medium', '2026-06-10 14:11:23'),
('8', NULL, 'admin', 'login_failed', 'auth', 'Failed login attempt for \'admin\'.', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'desktop', NULL, NULL, NULL, 'medium', '2026-06-10 14:12:04'),
('9', NULL, 'manager1', 'login_failed', 'auth', 'Failed login attempt for \'manager1\'.', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'desktop', NULL, NULL, NULL, 'medium', '2026-06-10 14:12:37'),
('10', NULL, 'sales1', 'login_failed', 'auth', 'Failed login attempt for \'sales1\'.', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'desktop', NULL, NULL, NULL, 'medium', '2026-06-10 14:13:29'),
('11', '1', 'admin', 'login', 'auth', 'User \'admin\' logged in.', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'desktop', NULL, NULL, NULL, 'low', '2026-06-10 14:17:11'),
('12', '1', 'admin', 'logout', 'auth', 'User \'admin\' logged out.', NULL, NULL, '::1', NULL, NULL, NULL, NULL, NULL, 'low', '2026-06-10 14:31:59'),
('13', '2', 'manager1', 'login', 'auth', 'User \'manager1\' logged in.', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'desktop', NULL, NULL, NULL, 'low', '2026-06-10 14:32:09'),
('14', '2', 'manager1', 'logout', 'auth', 'User \'manager1\' logged out.', NULL, NULL, '::1', NULL, NULL, NULL, NULL, NULL, 'low', '2026-06-10 14:32:49'),
('15', '3', 'sales1', 'login', 'auth', 'User \'sales1\' logged in.', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'desktop', NULL, NULL, NULL, 'low', '2026-06-10 14:32:57'),
('16', '3', 'sales1', 'logout', 'auth', 'User \'sales1\' logged out.', NULL, NULL, '::1', NULL, NULL, NULL, NULL, NULL, 'low', '2026-06-10 14:33:09'),
('17', '4', 'clerk1', 'login', 'auth', 'User \'clerk1\' logged in.', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'desktop', NULL, NULL, NULL, 'low', '2026-06-10 14:33:36'),
('18', '4', 'clerk1', 'stock_received', 'inventory', 'Stock received: 500 units of \'176-Egg Incubator\' (Batch: BATCH-202606-0001)', NULL, '{\"batch\":\"BATCH-202606-0001\",\"quantity\":500,\"product\":\"176-Egg Incubator\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'desktop', '26be6vfskduu5ofp29kpt4rd86', 'batch', '1', 'low', '2026-06-10 14:34:30'),
('19', '4', 'clerk1', 'stock_received', 'inventory', 'Stock received: 40 units of \'Digital Thermometer\' (Batch: BATCH-202606-0002)', NULL, '{\"batch\":\"BATCH-202606-0002\",\"quantity\":40,\"product\":\"Digital Thermometer\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'desktop', '26be6vfskduu5ofp29kpt4rd86', 'batch', '2', 'low', '2026-06-10 14:35:11'),
('20', '4', 'clerk1', 'stock_received', 'inventory', 'Stock received: 12 units of \'Automatic Turner Tray\' (Batch: BATCH-202606-0003)', NULL, '{\"batch\":\"BATCH-202606-0003\",\"quantity\":12,\"product\":\"Automatic Turner Tray\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'desktop', '26be6vfskduu5ofp29kpt4rd86', 'batch', '3', 'low', '2026-06-10 14:36:22'),
('21', '4', 'clerk1', 'logout', 'auth', 'User \'clerk1\' logged out.', NULL, NULL, '::1', NULL, NULL, NULL, NULL, NULL, 'low', '2026-06-10 14:36:34'),
('22', '3', 'sales1', 'login', 'auth', 'User \'sales1\' logged in.', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'desktop', NULL, NULL, NULL, 'low', '2026-06-10 14:36:41'),
('23', '3', 'sales1', 'sale_created', 'sales', 'Sale INV-202606-00001 created. Total: $195.00', NULL, '{\"invoice\":\"INV-202606-00001\",\"total\":195}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'desktop', '99aug2dvf2mghsgqtu0f1eb77h', 'sale', '1', 'low', '2026-06-10 14:37:33'),
('24', '3', 'sales1', 'sale_created', 'sales', 'Sale INV-202606-00002 created. Total: $23.75', NULL, '{\"invoice\":\"INV-202606-00002\",\"total\":23.75}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'desktop', '99aug2dvf2mghsgqtu0f1eb77h', 'sale', '2', 'low', '2026-06-10 14:40:17'),
('25', '3', 'sales1', 'sale_created', 'sales', 'Sale INV-202606-00003 created. Total: $23.75', NULL, '{\"invoice\":\"INV-202606-00003\",\"total\":23.75}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'desktop', '99aug2dvf2mghsgqtu0f1eb77h', 'sale', '3', 'low', '2026-06-10 14:45:28'),
('26', '3', 'sales1', 'sale_created', 'sales', 'Sale INV-202606-00004 created. Total: $237.50', NULL, '{\"invoice\":\"INV-202606-00004\",\"total\":237.5}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'desktop', '99aug2dvf2mghsgqtu0f1eb77h', 'sale', '4', 'low', '2026-06-10 14:46:59'),
('27', '3', 'sales1', 'sale_created', 'sales', 'Sale INV-202606-00005 created. Total: $71.00', NULL, '{\"invoice\":\"INV-202606-00005\",\"total\":71}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'desktop', '99aug2dvf2mghsgqtu0f1eb77h', 'sale', '5', 'low', '2026-06-10 14:49:36'),
('28', '3', 'sales1', 'sale_created', 'sales', 'Sale INV-202606-00006 created. Total: $105.00', NULL, '{\"invoice\":\"INV-202606-00006\",\"total\":105}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'desktop', '99aug2dvf2mghsgqtu0f1eb77h', 'sale', '6', 'low', '2026-06-10 14:51:26'),
('29', '3', 'sales1', 'sale_created', 'sales', 'Sale INV-202606-00007 created. Total: $270.00', NULL, '{\"invoice\":\"INV-202606-00007\",\"total\":270}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'desktop', '99aug2dvf2mghsgqtu0f1eb77h', 'sale', '7', 'low', '2026-06-10 14:53:02'),
('30', '3', 'sales1', 'logout', 'auth', 'User \'sales1\' logged out.', NULL, NULL, '::1', NULL, NULL, NULL, NULL, NULL, 'low', '2026-06-10 14:54:56'),
('31', '1', 'admin', 'login', 'auth', 'User \'admin\' logged in.', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'desktop', NULL, NULL, NULL, 'low', '2026-06-10 14:55:00'),
('32', '1', 'admin', 'logout', 'auth', 'User \'admin\' logged out.', NULL, NULL, '::1', NULL, NULL, NULL, NULL, NULL, 'low', '2026-06-10 15:44:42'),
('33', '1', 'admin', 'login', 'auth', 'User \'admin\' logged in.', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'desktop', NULL, NULL, NULL, 'low', '2026-06-10 15:50:51'),
('34', '1', 'admin', 'login', 'auth', 'User \'admin\' logged in.', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0', 'desktop', NULL, NULL, NULL, 'low', '2026-06-10 16:04:59'),
('35', '1', 'admin', 'sale_returned', 'sales', 'Return RET-3B72C9BF processed for invoice INV-202606-00007. Amount: $90.00', NULL, '{\"return_number\":\"RET-3B72C9BF\",\"amount\":90}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'desktop', 'umreklj9elaj530auvuok9jb4r', 'return', '1', 'medium', '2026-06-10 16:24:55'),
('36', '1', 'admin', 'stock_received', 'inventory', 'Stock received: 67 units of \'56-Egg Incubator\' (Batch: BATCH-202606-0004)', NULL, '{\"batch\":\"BATCH-202606-0004\",\"quantity\":67,\"product\":\"56-Egg Incubator\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'desktop', 'umreklj9elaj530auvuok9jb4r', 'batch', '4', 'low', '2026-06-10 16:52:46'),
('37', '1', 'admin', 'logout', 'auth', 'User \'admin\' logged out.', NULL, NULL, '::1', NULL, NULL, NULL, NULL, NULL, 'low', '2026-06-10 16:55:54'),
('38', '3', 'sales1', 'login', 'auth', 'User \'sales1\' logged in.', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'desktop', NULL, NULL, NULL, 'low', '2026-06-10 16:56:01'),
('39', '3', 'sales1', 'sale_created', 'sales', 'Sale INV-202606-00008 created. Total: $110.00', NULL, '{\"invoice\":\"INV-202606-00008\",\"total\":110}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'desktop', 'q1b6nfebg768i49j403jqqnfoh', 'sale', '8', 'low', '2026-06-10 16:57:15'),
('40', '3', 'sales1', 'logout', 'auth', 'User \'sales1\' logged out.', NULL, NULL, '::1', NULL, NULL, NULL, NULL, NULL, 'low', '2026-06-10 16:58:20'),
('41', '4', 'clerk1', 'login', 'auth', 'User \'clerk1\' logged in.', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'desktop', NULL, NULL, NULL, 'low', '2026-06-10 16:58:28'),
('42', '4', 'clerk1', 'logout', 'auth', 'User \'clerk1\' logged out.', NULL, NULL, '::1', NULL, NULL, NULL, NULL, NULL, 'low', '2026-06-10 16:59:26'),
('43', '2', 'manager1', 'login', 'auth', 'User \'manager1\' logged in.', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'desktop', NULL, NULL, NULL, 'low', '2026-06-10 16:59:35'),
('44', '2', 'manager1', 'logout', 'auth', 'User \'manager1\' logged out.', NULL, NULL, '::1', NULL, NULL, NULL, NULL, NULL, 'low', '2026-06-10 17:00:59'),
('45', '1', 'admin', 'login', 'auth', 'User \'admin\' logged in.', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'desktop', NULL, NULL, NULL, 'low', '2026-06-10 17:01:01'),
('46', '1', 'admin', 'login', 'auth', 'User \'admin\' logged in.', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'desktop', NULL, NULL, NULL, 'low', '2026-06-12 07:27:48');

DROP TABLE IF EXISTS `backup_logs`;
CREATE TABLE `backup_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `backup_type` enum('manual','scheduled','auto') NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_size` bigint(20) unsigned DEFAULT NULL,
  `status` enum('success','failed','in_progress') NOT NULL,
  `notes` text DEFAULT NULL,
  `triggered_by` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `triggered_by` (`triggered_by`),
  CONSTRAINT `backup_logs_ibfk_1` FOREIGN KEY (`triggered_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `backup_logs` (`id`, `backup_type`, `file_name`, `file_path`, `file_size`, `status`, `notes`, `triggered_by`, `created_at`) VALUES
('1', 'manual', 'backup_incubator_system_2026-06-12_073047.sql', 'C:\\xampp\\htdocs\\incubator_system/storage/backups/backup_incubator_system_2026-06-12_073047.sql', NULL, 'in_progress', NULL, '1', '2026-06-12 07:30:47');

DROP TABLE IF EXISTS `customers`;
CREATE TABLE `customers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `customer_code` varchar(20) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT 'Zimbabwe',
  `notes` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `customer_code` (`customer_code`),
  KEY `created_by` (`created_by`),
  KEY `idx_code` (`customer_code`),
  KEY `idx_name` (`full_name`),
  KEY `idx_phone` (`phone`),
  FULLTEXT KEY `idx_search` (`full_name`,`email`,`phone`),
  CONSTRAINT `customers_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `customers` (`id`, `customer_code`, `full_name`, `email`, `phone`, `address`, `city`, `country`, `notes`, `is_active`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
('1', 'CUST-0001', 'Blessing Chirwa', 'blessing.chirwa@email.com', '+263771100001', '12 Harare Drive', 'Harare', 'Zimbabwe', NULL, '1', '1', '2026-06-10 14:05:15', '2026-06-10 14:05:15', NULL),
('2', 'CUST-0002', 'Takudzwa Mhembere', 'taku.m@gmail.com', '+263772200002', '45 Bulawayo Rd', 'Bulawayo', 'Zimbabwe', NULL, '1', '1', '2026-06-10 14:05:15', '2026-06-10 14:05:15', NULL),
('3', 'CUST-0003', 'Nyasha Farm Ltd', 'nyashafarm@business.co.zw', '+263773300003', 'Plot 23 Mazowe', 'Mazowe', 'Zimbabwe', NULL, '1', '1', '2026-06-10 14:05:15', '2026-06-10 14:05:15', NULL),
('4', 'CUST-0004', 'Farai Muradzikwa', 'farai.m@yahoo.com', '+263774400004', '8 Mutare Ave', 'Mutare', 'Zimbabwe', NULL, '1', '1', '2026-06-10 14:05:15', '2026-06-10 14:05:15', NULL),
('5', 'CUST-0005', 'Sunshine Poultry', 'info@sunshinepoultry.co.zw', '+263775500005', '100 Gweru Industrial', 'Gweru', 'Zimbabwe', NULL, '1', '1', '2026-06-10 14:05:15', '2026-06-10 14:05:15', NULL);

DROP TABLE IF EXISTS `daily_ledger`;
CREATE TABLE `daily_ledger` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ledger_date` date NOT NULL,
  `total_transactions` int(10) unsigned NOT NULL DEFAULT 0,
  `total_units_sold` int(10) unsigned NOT NULL DEFAULT 0,
  `total_revenue` decimal(14,2) NOT NULL DEFAULT 0.00,
  `total_inventory_cost` decimal(14,2) NOT NULL DEFAULT 0.00,
  `gross_profit` decimal(14,2) NOT NULL DEFAULT 0.00,
  `total_returns` decimal(14,2) NOT NULL DEFAULT 0.00,
  `net_profit` decimal(14,2) NOT NULL DEFAULT 0.00,
  `total_discounts` decimal(14,2) NOT NULL DEFAULT 0.00,
  `total_tax` decimal(14,2) NOT NULL DEFAULT 0.00,
  `cash_sales` decimal(14,2) NOT NULL DEFAULT 0.00,
  `ecocash_sales` decimal(14,2) NOT NULL DEFAULT 0.00,
  `other_sales` decimal(14,2) NOT NULL DEFAULT 0.00,
  `opening_stock_value` decimal(14,2) NOT NULL DEFAULT 0.00,
  `closing_stock_value` decimal(14,2) NOT NULL DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `finalized` tinyint(1) NOT NULL DEFAULT 0,
  `finalized_by` int(10) unsigned DEFAULT NULL,
  `finalized_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `ledger_date` (`ledger_date`),
  KEY `idx_date` (`ledger_date`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `daily_ledger` (`id`, `ledger_date`, `total_transactions`, `total_units_sold`, `total_revenue`, `total_inventory_cost`, `gross_profit`, `total_returns`, `net_profit`, `total_discounts`, `total_tax`, `cash_sales`, `ecocash_sales`, `other_sales`, `opening_stock_value`, `closing_stock_value`, `notes`, `finalized`, `finalized_by`, `finalized_at`, `created_at`, `updated_at`) VALUES
('1', '2026-06-10', '7', '54', '946.00', '742.46', '203.54', '90.00', '203.54', '0.00', '0.00', '457.50', '507.50', '71.00', '0.00', '0.00', NULL, '0', NULL, NULL, '2026-06-10 14:37:33', '2026-06-10 16:57:15');

DROP TABLE IF EXISTS `generated_reports`;
CREATE TABLE `generated_reports` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `report_type` varchar(50) NOT NULL,
  `report_name` varchar(200) NOT NULL,
  `parameters` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`parameters`)),
  `file_path` varchar(255) DEFAULT NULL,
  `file_format` enum('pdf','excel','csv') NOT NULL,
  `file_size` int(10) unsigned DEFAULT NULL,
  `status` enum('generating','ready','failed') NOT NULL DEFAULT 'generating',
  `generated_by` int(10) unsigned DEFAULT NULL,
  `generated_at` datetime NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime DEFAULT NULL,
  `download_count` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `generated_by` (`generated_by`),
  KEY `idx_type` (`report_type`),
  KEY `idx_generated_at` (`generated_at`),
  CONSTRAINT `generated_reports_ibfk_1` FOREIGN KEY (`generated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `inventory_batches`;
CREATE TABLE `inventory_batches` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(10) unsigned NOT NULL,
  `batch_code` varchar(50) NOT NULL,
  `supplier_name` varchar(150) DEFAULT NULL,
  `supplier_ref` varchar(100) DEFAULT NULL,
  `quantity_received` int(10) unsigned NOT NULL DEFAULT 0,
  `quantity_current` int(10) unsigned NOT NULL DEFAULT 0,
  `quantity_sold` int(10) unsigned NOT NULL DEFAULT 0,
  `quantity_damaged` int(10) unsigned NOT NULL DEFAULT 0,
  `quantity_adjusted` int(11) NOT NULL DEFAULT 0 COMMENT 'Positive=added, Negative=removed',
  `cost_price` decimal(12,2) NOT NULL,
  `selling_price` decimal(12,2) NOT NULL,
  `acquisition_date` date NOT NULL,
  `expiry_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('active','depleted','archived') NOT NULL DEFAULT 'active',
  `total_revenue` decimal(14,2) NOT NULL DEFAULT 0.00,
  `total_cost` decimal(14,2) NOT NULL DEFAULT 0.00,
  `gross_profit` decimal(14,2) NOT NULL DEFAULT 0.00,
  `net_profit` decimal(14,2) NOT NULL DEFAULT 0.00,
  `loss_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `depleted_at` datetime DEFAULT NULL,
  `archived_at` datetime DEFAULT NULL,
  `archived_by` int(10) unsigned DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `batch_code` (`batch_code`),
  KEY `created_by` (`created_by`),
  KEY `archived_by` (`archived_by`),
  KEY `idx_product` (`product_id`),
  KEY `idx_status` (`status`),
  KEY `idx_batch_code` (`batch_code`),
  KEY `idx_acquisition` (`acquisition_date`),
  CONSTRAINT `inventory_batches_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `inventory_batches_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `inventory_batches_ibfk_3` FOREIGN KEY (`archived_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `inventory_batches` (`id`, `product_id`, `batch_code`, `supplier_name`, `supplier_ref`, `quantity_received`, `quantity_current`, `quantity_sold`, `quantity_damaged`, `quantity_adjusted`, `cost_price`, `selling_price`, `acquisition_date`, `expiry_date`, `notes`, `status`, `total_revenue`, `total_cost`, `gross_profit`, `net_profit`, `loss_amount`, `depleted_at`, `archived_at`, `archived_by`, `created_by`, `created_at`, `updated_at`) VALUES
('1', '5', 'BATCH-202606-0001', 'Chris Diza', 'TX06102026', '500', '498', '2', '0', '0', '25.00', '35.50', '2026-06-10', '2026-08-10', '', 'active', '71.00', '50.00', '21.00', '0.00', '0.00', NULL, NULL, NULL, '4', '2026-06-10 14:34:30', '2026-06-10 14:49:36'),
('2', '8', 'BATCH-202606-0002', 'Victor', 'TX061020266', '40', '8', '32', '0', '0', '9.17', '15.00', '2026-06-10', '2026-07-22', '', 'active', '480.00', '293.44', '186.56', '0.00', '0.00', NULL, NULL, NULL, '4', '2026-06-10 14:35:11', '2026-06-10 16:24:55'),
('3', '10', 'BATCH-202606-0003', 'Victor Max', 'TX0610202665', '12', '0', '12', '0', '0', '23.00', '23.75', '2026-06-10', '2026-06-25', '', 'depleted', '285.00', '276.00', '9.00', '0.00', '0.00', '2026-06-10 14:46:59', NULL, NULL, '4', '2026-06-10 14:36:22', '2026-06-10 14:46:59'),
('4', '2', 'BATCH-202606-0004', 'Chris Diza', 'TX061020266', '67', '65', '2', '0', '0', '34.00', '55.00', '2026-06-10', '2026-07-24', '', 'active', '110.00', '68.00', '42.00', '0.00', '0.00', NULL, NULL, NULL, '1', '2026-06-10 16:52:46', '2026-06-10 16:57:15');

DROP TABLE IF EXISTS `inventory_movements`;
CREATE TABLE `inventory_movements` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `batch_id` int(10) unsigned NOT NULL,
  `product_id` int(10) unsigned NOT NULL,
  `movement_type` enum('receive','sale','return','adjustment','damage','transfer_in','transfer_out') NOT NULL,
  `reference_type` varchar(50) DEFAULT NULL COMMENT 'sale, return, adjustment, etc.',
  `reference_id` int(10) unsigned DEFAULT NULL,
  `quantity_before` int(11) NOT NULL,
  `quantity_change` int(11) NOT NULL COMMENT 'Positive=in, Negative=out',
  `quantity_after` int(11) NOT NULL,
  `unit_cost` decimal(12,2) DEFAULT NULL,
  `unit_price` decimal(12,2) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `performed_by` int(10) unsigned DEFAULT NULL,
  `performed_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `performed_by` (`performed_by`),
  KEY `idx_batch` (`batch_id`),
  KEY `idx_product` (`product_id`),
  KEY `idx_type` (`movement_type`),
  KEY `idx_performed_at` (`performed_at`),
  KEY `idx_reference` (`reference_type`,`reference_id`),
  CONSTRAINT `inventory_movements_ibfk_1` FOREIGN KEY (`batch_id`) REFERENCES `inventory_batches` (`id`),
  CONSTRAINT `inventory_movements_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `inventory_movements_ibfk_3` FOREIGN KEY (`performed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `inventory_movements` (`id`, `batch_id`, `product_id`, `movement_type`, `reference_type`, `reference_id`, `quantity_before`, `quantity_change`, `quantity_after`, `unit_cost`, `unit_price`, `notes`, `performed_by`, `performed_at`) VALUES
('1', '1', '5', 'receive', NULL, NULL, '0', '500', '500', '25.00', '35.50', 'Initial stock receipt', '4', '2026-06-10 14:34:30'),
('2', '2', '8', 'receive', NULL, NULL, '0', '40', '40', '9.17', '15.00', 'Initial stock receipt', '4', '2026-06-10 14:35:11'),
('3', '3', '10', 'receive', NULL, NULL, '0', '12', '12', '23.00', '23.75', 'Initial stock receipt', '4', '2026-06-10 14:36:22'),
('4', '2', '8', 'sale', 'sale', '1', '40', '-13', '27', '9.17', '15.00', NULL, '3', '2026-06-10 14:37:33'),
('5', '3', '10', 'sale', 'sale', '2', '12', '-1', '11', '23.00', '23.75', NULL, '3', '2026-06-10 14:40:17'),
('6', '3', '10', 'sale', 'sale', '3', '11', '-1', '10', '23.00', '23.75', NULL, '3', '2026-06-10 14:45:28'),
('7', '3', '10', 'sale', 'sale', '4', '10', '-10', '0', '23.00', '23.75', NULL, '3', '2026-06-10 14:46:59'),
('8', '1', '5', 'sale', 'sale', '5', '500', '-2', '498', '25.00', '35.50', NULL, '3', '2026-06-10 14:49:36'),
('9', '2', '8', 'sale', 'sale', '6', '27', '-7', '20', '9.17', '15.00', NULL, '3', '2026-06-10 14:51:26'),
('10', '2', '8', 'sale', 'sale', '7', '20', '-18', '2', '9.17', '15.00', NULL, '3', '2026-06-10 14:53:02'),
('11', '2', '8', 'return', 'return', '1', '2', '6', '8', '9.17', '15.00', NULL, '1', '2026-06-10 16:24:55'),
('12', '4', '2', 'receive', NULL, NULL, '0', '67', '67', '34.00', '55.00', 'Initial stock receipt', '1', '2026-06-10 16:52:46'),
('13', '4', '2', 'sale', 'sale', '8', '67', '-2', '65', '34.00', '55.00', NULL, '3', '2026-06-10 16:57:15');

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(50) NOT NULL,
  `title` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `severity` enum('info','success','warning','danger') NOT NULL DEFAULT 'info',
  `icon` varchar(50) DEFAULT NULL,
  `reference_type` varchar(50) DEFAULT NULL,
  `reference_id` int(10) unsigned DEFAULT NULL,
  `target_role` int(10) unsigned DEFAULT NULL COMMENT 'NULL = all roles',
  `target_user` int(10) unsigned DEFAULT NULL COMMENT 'NULL = broadcast to role',
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `read_at` datetime DEFAULT NULL,
  `read_by` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_target_user` (`target_user`),
  KEY `idx_target_role` (`target_role`),
  KEY `idx_type` (`type`),
  KEY `idx_created` (`created_at`),
  KEY `idx_read` (`is_read`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`target_role`) REFERENCES `roles` (`id`) ON DELETE SET NULL,
  CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`target_user`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `notifications` (`id`, `type`, `title`, `message`, `severity`, `icon`, `reference_type`, `reference_id`, `target_role`, `target_user`, `is_read`, `read_at`, `read_by`, `created_at`, `expires_at`) VALUES
('1', 'failed_login', 'Failed Login Attempt', 'Failed login attempt #1 for username \'admin\' from ::1', 'warning', 'bi-shield-exclamation', NULL, NULL, '1', NULL, '0', NULL, NULL, '2026-06-10 14:10:37', NULL),
('2', 'failed_login', 'Failed Login Attempt', 'Failed login attempt #2 for username \'admin\' from ::1', 'warning', 'bi-shield-exclamation', NULL, NULL, '1', NULL, '0', NULL, NULL, '2026-06-10 14:11:07', NULL),
('3', 'failed_login', 'Failed Login Attempt', 'Failed login attempt #3 for username \'admin\' from ::1', 'danger', 'bi-shield-exclamation', NULL, NULL, '1', NULL, '0', NULL, NULL, '2026-06-10 14:11:23', NULL),
('4', 'failed_login', 'Failed Login Attempt', 'Failed login attempt #4 for username \'admin\' from ::1', 'danger', 'bi-shield-exclamation', NULL, NULL, '1', NULL, '0', NULL, NULL, '2026-06-10 14:12:04', NULL),
('5', 'failed_login', 'Failed Login Attempt', 'Failed login attempt #1 for username \'manager1\' from ::1', 'warning', 'bi-shield-exclamation', NULL, NULL, '1', NULL, '0', NULL, NULL, '2026-06-10 14:12:37', NULL),
('6', 'failed_login', 'Failed Login Attempt', 'Failed login attempt #1 for username \'sales1\' from ::1', 'warning', 'bi-shield-exclamation', NULL, NULL, '1', NULL, '0', NULL, NULL, '2026-06-10 14:13:29', NULL),
('7', 'new_stock', 'New Stock Received', '500 units of \'176-Egg Incubator\' received (Batch: BATCH-202606-0001).', 'success', 'bi-box-arrow-in-down', 'batch', '1', NULL, NULL, '0', NULL, NULL, '2026-06-10 14:34:30', NULL),
('8', 'new_stock', 'New Stock Received', '40 units of \'Digital Thermometer\' received (Batch: BATCH-202606-0002).', 'success', 'bi-box-arrow-in-down', 'batch', '2', NULL, NULL, '0', NULL, NULL, '2026-06-10 14:35:11', NULL),
('9', 'new_stock', 'New Stock Received', '12 units of \'Automatic Turner Tray\' received (Batch: BATCH-202606-0003).', 'success', 'bi-box-arrow-in-down', 'batch', '3', NULL, NULL, '0', NULL, NULL, '2026-06-10 14:36:22', NULL),
('10', 'sale_completed', 'Sale Completed', 'Invoice INV-202606-00001 completed. Total: $195.00', 'success', 'bi-receipt', 'sale', '1', NULL, NULL, '0', NULL, NULL, '2026-06-10 14:37:33', NULL),
('11', 'sale_completed', 'Sale Completed', 'Invoice INV-202606-00002 completed. Total: $23.75', 'success', 'bi-receipt', 'sale', '2', NULL, NULL, '0', NULL, NULL, '2026-06-10 14:40:17', NULL),
('12', 'sale_completed', 'Sale Completed', 'Invoice INV-202606-00003 completed. Total: $23.75', 'success', 'bi-receipt', 'sale', '3', NULL, NULL, '0', NULL, NULL, '2026-06-10 14:45:28', NULL),
('13', 'out_of_stock', 'Out of Stock', '\'Automatic Turner Tray\' is now OUT OF STOCK. Please replenish immediately.', 'danger', 'bi-x-circle', 'product', '10', NULL, NULL, '0', NULL, NULL, '2026-06-10 14:46:59', NULL),
('14', 'sale_completed', 'Sale Completed', 'Invoice INV-202606-00004 completed. Total: $237.50', 'success', 'bi-receipt', 'sale', '4', NULL, NULL, '0', NULL, NULL, '2026-06-10 14:46:59', NULL),
('15', 'sale_completed', 'Sale Completed', 'Invoice INV-202606-00005 completed. Total: $71.00', 'success', 'bi-receipt', 'sale', '5', NULL, NULL, '0', NULL, NULL, '2026-06-10 14:49:36', NULL),
('16', 'sale_completed', 'Sale Completed', 'Invoice INV-202606-00006 completed. Total: $105.00', 'success', 'bi-receipt', 'sale', '6', NULL, NULL, '0', NULL, NULL, '2026-06-10 14:51:26', NULL),
('17', 'low_stock', 'Low Stock Alert', '\'Digital Thermometer\' is running low (2 units remaining, threshold: 10).', 'warning', 'bi-exclamation-triangle', 'product', '8', NULL, NULL, '0', NULL, NULL, '2026-06-10 14:53:02', NULL),
('18', 'sale_completed', 'Sale Completed', 'Invoice INV-202606-00007 completed. Total: $270.00', 'success', 'bi-receipt', 'sale', '7', NULL, NULL, '0', NULL, NULL, '2026-06-10 14:53:02', NULL),
('19', 'new_stock', 'New Stock Received', '67 units of \'56-Egg Incubator\' received (Batch: BATCH-202606-0004).', 'success', 'bi-box-arrow-in-down', 'batch', '4', NULL, NULL, '0', NULL, NULL, '2026-06-10 16:52:46', NULL),
('20', 'sale_completed', 'Sale Completed', 'Invoice INV-202606-00008 completed. Total: $110.00', 'success', 'bi-receipt', 'sale', '8', NULL, NULL, '0', NULL, NULL, '2026-06-10 16:57:15', NULL);

DROP TABLE IF EXISTS `permissions`;
CREATE TABLE `permissions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `module` varchar(50) NOT NULL,
  `action` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `idx_module` (`module`),
  KEY `idx_action` (`action`)
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `permissions` (`id`, `module`, `action`, `name`, `description`, `created_at`) VALUES
('1', 'dashboard', 'view', 'dashboard.view', 'View dashboard', '2026-06-10 14:05:15'),
('2', 'users', 'view', 'users.view', 'View user list', '2026-06-10 14:05:15'),
('3', 'users', 'create', 'users.create', 'Create new users', '2026-06-10 14:05:15'),
('4', 'users', 'edit', 'users.edit', 'Edit user details', '2026-06-10 14:05:15'),
('5', 'users', 'delete', 'users.delete', 'Delete users', '2026-06-10 14:05:15'),
('6', 'users', 'change_role', 'users.change_role', 'Change user roles', '2026-06-10 14:05:15'),
('7', 'users', 'unlock', 'users.unlock', 'Unlock locked accounts', '2026-06-10 14:05:15'),
('8', 'inventory', 'view', 'inventory.view', 'View inventory', '2026-06-10 14:05:15'),
('9', 'inventory', 'receive', 'inventory.receive', 'Receive new stock', '2026-06-10 14:05:15'),
('10', 'inventory', 'edit', 'inventory.edit', 'Edit inventory details', '2026-06-10 14:05:15'),
('11', 'inventory', 'adjust', 'inventory.adjust', 'Adjust stock quantities', '2026-06-10 14:05:15'),
('12', 'inventory', 'delete', 'inventory.delete', 'Delete inventory records', '2026-06-10 14:05:15'),
('13', 'inventory', 'view_cost', 'inventory.view_cost', 'View cost prices', '2026-06-10 14:05:15'),
('14', 'products', 'view', 'products.view', 'View products', '2026-06-10 14:05:15'),
('15', 'products', 'create', 'products.create', 'Create products', '2026-06-10 14:05:15'),
('16', 'products', 'edit', 'products.edit', 'Edit products', '2026-06-10 14:05:15'),
('17', 'products', 'delete', 'products.delete', 'Delete products', '2026-06-10 14:05:15'),
('18', 'sales', 'view', 'sales.view', 'View sales records', '2026-06-10 14:05:15'),
('19', 'sales', 'create', 'sales.create', 'Process new sales', '2026-06-10 14:05:15'),
('20', 'sales', 'edit', 'sales.edit', 'Edit sale records', '2026-06-10 14:05:15'),
('21', 'sales', 'void', 'sales.void', 'Void sales', '2026-06-10 14:05:15'),
('22', 'sales', 'return', 'sales.return', 'Process returns', '2026-06-10 14:05:15'),
('23', 'sales', 'view_profit', 'sales.view_profit', 'View profit data on sales', '2026-06-10 14:05:15'),
('24', 'customers', 'view', 'customers.view', 'View customers', '2026-06-10 14:05:15'),
('25', 'customers', 'create', 'customers.create', 'Create customers', '2026-06-10 14:05:15'),
('26', 'customers', 'edit', 'customers.edit', 'Edit customer details', '2026-06-10 14:05:15'),
('27', 'customers', 'delete', 'customers.delete', 'Delete customers', '2026-06-10 14:05:15'),
('28', 'reports', 'view', 'reports.view', 'View reports', '2026-06-10 14:05:15'),
('29', 'reports', 'generate', 'reports.generate', 'Generate reports', '2026-06-10 14:05:15'),
('30', 'reports', 'export', 'reports.export', 'Export reports', '2026-06-10 14:05:15'),
('31', 'reports', 'financial', 'reports.financial', 'View financial reports', '2026-06-10 14:05:15'),
('32', 'pnl', 'view', 'pnl.view', 'View P&L reports', '2026-06-10 14:05:15'),
('33', 'audit', 'view', 'audit.view', 'View audit logs', '2026-06-10 14:05:15'),
('34', 'audit', 'export', 'audit.export', 'Export audit logs', '2026-06-10 14:05:15'),
('35', 'notifications', 'manage', 'notifications.manage', 'Manage notifications', '2026-06-10 14:05:15'),
('36', 'settings', 'view', 'settings.view', 'View settings', '2026-06-10 14:05:15'),
('37', 'settings', 'edit', 'settings.edit', 'Edit settings', '2026-06-10 14:05:15'),
('38', 'settings', 'backup', 'settings.backup', 'Manage backups', '2026-06-10 14:05:15');

DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sku` varchar(50) NOT NULL,
  `name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `capacity` int(10) unsigned DEFAULT NULL COMMENT 'Egg capacity',
  `brand` varchar(100) DEFAULT NULL,
  `model` varchar(100) DEFAULT NULL,
  `unit` varchar(20) NOT NULL DEFAULT 'unit',
  `low_stock_threshold` int(10) unsigned NOT NULL DEFAULT 5,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `image_path` varchar(255) DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `sku` (`sku`),
  KEY `created_by` (`created_by`),
  KEY `idx_sku` (`sku`),
  FULLTEXT KEY `idx_search` (`name`,`description`,`brand`,`model`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `products` (`id`, `sku`, `name`, `description`, `category`, `capacity`, `brand`, `model`, `unit`, `low_stock_threshold`, `is_active`, `image_path`, `created_by`, `created_at`, `updated_at`) VALUES
('1', 'INC-48', '48-Egg Incubator', 'Fully automatic 48-egg incubator with digital temperature control and automatic egg turning', 'Automatic', '48', 'HatchPro', 'HP-48A', 'unit', '5', '1', NULL, '1', '2026-06-10 14:05:15', '2026-06-10 14:05:15'),
('2', 'INC-56', '56-Egg Incubator', 'Semi-automatic 56-egg incubator with humidity control', 'Semi-Auto', '56', 'HatchPro', 'HP-56S', 'unit', '3', '1', NULL, '1', '2026-06-10 14:05:15', '2026-06-10 14:05:15'),
('3', 'INC-96', '96-Egg Incubator', 'Commercial 96-egg incubator with LED candler and dual power supply', 'Commercial', '96', 'FarmTech', 'FT-96C', 'unit', '3', '1', NULL, '1', '2026-06-10 14:05:15', '2026-06-10 14:05:15'),
('4', 'INC-128', '128-Egg Incubator', 'Large capacity 128-egg automatic incubator for small farms', 'Commercial', '128', 'FarmTech', 'FT-128A', 'unit', '2', '1', NULL, '1', '2026-06-10 14:05:15', '2026-06-10 14:05:15'),
('5', 'INC-176', '176-Egg Incubator', 'High-capacity automatic incubator with solar backup compatibility', 'Industrial', '176', 'AgroPro', 'AP-176S', 'unit', '2', '1', NULL, '1', '2026-06-10 14:05:15', '2026-06-10 14:05:15'),
('6', 'INC-264', '264-Egg Incubator', 'Industrial-grade 264-egg incubator with external humidity sensor', 'Industrial', '264', 'AgroPro', 'AP-264I', 'unit', '1', '1', NULL, '1', '2026-06-10 14:05:15', '2026-06-10 14:05:15'),
('7', 'INC-500', '500-Egg Incubator', 'Commercial 500-egg incubator for poultry farms', 'Industrial', '500', 'ProHatch', 'PH-500C', 'unit', '1', '1', NULL, '1', '2026-06-10 14:05:15', '2026-06-10 14:05:15'),
('8', 'ACC-THERM', 'Digital Thermometer', 'High precision digital thermometer with hygrometer for incubator monitoring', 'Accessory', NULL, 'HatchPro', 'ACC-TH1', 'unit', '10', '1', NULL, '1', '2026-06-10 14:05:15', '2026-06-10 14:05:15'),
('9', 'ACC-CNDL', 'Egg Candler', 'LED egg candler for fertility checking', 'Accessory', NULL, 'FarmTech', 'ACC-CL1', 'unit', '10', '1', NULL, '1', '2026-06-10 14:05:15', '2026-06-10 14:05:15'),
('10', 'ACC-TRNY', 'Automatic Turner Tray', 'Replacement automatic egg turner tray, compatible with most models', 'Accessory', NULL, 'HatchPro', 'ACC-TT1', 'unit', '5', '1', NULL, '1', '2026-06-10 14:05:15', '2026-06-10 14:05:15');

DROP TABLE IF EXISTS `return_items`;
CREATE TABLE `return_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `return_id` int(10) unsigned NOT NULL,
  `sale_item_id` int(10) unsigned NOT NULL,
  `product_id` int(10) unsigned NOT NULL,
  `batch_id` int(10) unsigned NOT NULL,
  `quantity` int(10) unsigned NOT NULL,
  `unit_price` decimal(12,2) NOT NULL,
  `line_total` decimal(14,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `return_id` (`return_id`),
  KEY `sale_item_id` (`sale_item_id`),
  KEY `product_id` (`product_id`),
  KEY `batch_id` (`batch_id`),
  CONSTRAINT `return_items_ibfk_1` FOREIGN KEY (`return_id`) REFERENCES `sale_returns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `return_items_ibfk_2` FOREIGN KEY (`sale_item_id`) REFERENCES `sale_items` (`id`),
  CONSTRAINT `return_items_ibfk_3` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `return_items_ibfk_4` FOREIGN KEY (`batch_id`) REFERENCES `inventory_batches` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `return_items` (`id`, `return_id`, `sale_item_id`, `product_id`, `batch_id`, `quantity`, `unit_price`, `line_total`) VALUES
('1', '1', '7', '8', '2', '6', '15.00', '90.00');

DROP TABLE IF EXISTS `role_permissions`;
CREATE TABLE `role_permissions` (
  `role_id` int(10) unsigned NOT NULL,
  `permission_id` int(10) unsigned NOT NULL,
  `granted_at` datetime NOT NULL DEFAULT current_timestamp(),
  `granted_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`role_id`,`permission_id`),
  KEY `permission_id` (`permission_id`),
  CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `role_permissions` (`role_id`, `permission_id`, `granted_at`, `granted_by`) VALUES
('1', '1', '2026-06-10 14:05:15', NULL),
('1', '2', '2026-06-10 14:05:15', NULL),
('1', '3', '2026-06-10 14:05:15', NULL),
('1', '4', '2026-06-10 14:05:15', NULL),
('1', '5', '2026-06-10 14:05:15', NULL),
('1', '6', '2026-06-10 14:05:15', NULL),
('1', '7', '2026-06-10 14:05:15', NULL),
('1', '8', '2026-06-10 14:05:15', NULL),
('1', '9', '2026-06-10 14:05:15', NULL),
('1', '10', '2026-06-10 14:05:15', NULL),
('1', '11', '2026-06-10 14:05:15', NULL),
('1', '12', '2026-06-10 14:05:15', NULL),
('1', '13', '2026-06-10 14:05:15', NULL),
('1', '14', '2026-06-10 14:05:15', NULL),
('1', '15', '2026-06-10 14:05:15', NULL),
('1', '16', '2026-06-10 14:05:15', NULL),
('1', '17', '2026-06-10 14:05:15', NULL),
('1', '18', '2026-06-10 14:05:15', NULL),
('1', '19', '2026-06-10 14:05:15', NULL),
('1', '20', '2026-06-10 14:05:15', NULL),
('1', '21', '2026-06-10 14:05:15', NULL),
('1', '22', '2026-06-10 14:05:15', NULL),
('1', '23', '2026-06-10 14:05:15', NULL),
('1', '24', '2026-06-10 14:05:15', NULL),
('1', '25', '2026-06-10 14:05:15', NULL),
('1', '26', '2026-06-10 14:05:15', NULL),
('1', '27', '2026-06-10 14:05:15', NULL),
('1', '28', '2026-06-10 14:05:15', NULL),
('1', '29', '2026-06-10 14:05:15', NULL),
('1', '30', '2026-06-10 14:05:15', NULL),
('1', '31', '2026-06-10 14:05:15', NULL),
('1', '32', '2026-06-10 14:05:15', NULL),
('1', '33', '2026-06-10 14:05:15', NULL),
('1', '34', '2026-06-10 14:05:15', NULL),
('1', '35', '2026-06-10 14:05:15', NULL),
('1', '36', '2026-06-10 14:05:15', NULL),
('1', '37', '2026-06-10 14:05:15', NULL),
('1', '38', '2026-06-10 14:05:15', NULL),
('2', '1', '2026-06-10 14:05:15', NULL),
('2', '8', '2026-06-10 14:05:15', NULL),
('2', '9', '2026-06-10 14:05:15', NULL),
('2', '10', '2026-06-10 14:05:15', NULL),
('2', '11', '2026-06-10 14:05:15', NULL),
('2', '12', '2026-06-10 14:05:15', NULL),
('2', '13', '2026-06-10 14:05:15', NULL),
('2', '14', '2026-06-10 14:05:15', NULL),
('2', '15', '2026-06-10 14:05:15', NULL),
('2', '16', '2026-06-10 14:05:15', NULL),
('2', '17', '2026-06-10 14:05:15', NULL),
('2', '18', '2026-06-10 14:05:15', NULL),
('2', '24', '2026-06-10 14:05:15', NULL),
('2', '25', '2026-06-10 14:05:15', NULL),
('2', '26', '2026-06-10 14:05:15', NULL),
('2', '27', '2026-06-10 14:05:15', NULL),
('2', '28', '2026-06-10 14:05:15', NULL),
('2', '29', '2026-06-10 14:05:15', NULL),
('2', '30', '2026-06-10 14:05:15', NULL),
('2', '31', '2026-06-10 14:05:15', NULL),
('2', '32', '2026-06-10 14:05:15', NULL),
('2', '35', '2026-06-10 14:05:15', NULL),
('3', '1', '2026-06-10 14:05:15', NULL),
('3', '8', '2026-06-10 14:05:15', NULL),
('3', '14', '2026-06-10 14:05:15', NULL),
('3', '18', '2026-06-10 14:05:15', NULL),
('3', '19', '2026-06-10 14:05:15', NULL),
('3', '22', '2026-06-10 14:05:15', NULL),
('3', '24', '2026-06-10 14:05:15', NULL),
('3', '25', '2026-06-10 14:05:15', NULL),
('3', '26', '2026-06-10 14:05:15', NULL),
('4', '1', '2026-06-10 14:05:15', NULL),
('4', '8', '2026-06-10 14:05:15', NULL),
('4', '9', '2026-06-10 14:05:15', NULL),
('4', '11', '2026-06-10 14:05:15', NULL),
('4', '14', '2026-06-10 14:05:15', NULL);

DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `display_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `is_system` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `roles` (`id`, `name`, `display_name`, `description`, `is_system`, `created_at`, `updated_at`) VALUES
('1', 'administrator', 'Administrator', 'Full system access with all permissions', '1', '2026-06-10 14:05:15', '2026-06-10 14:05:15'),
('2', 'manager', 'Manager', 'Manage inventory, sales, reports and users', '1', '2026-06-10 14:05:15', '2026-06-10 14:05:15'),
('3', 'sales_officer', 'Sales Officer', 'Process sales, view inventory and customers', '1', '2026-06-10 14:05:15', '2026-06-10 14:05:15'),
('4', 'stock_clerk', 'Stock Clerk', 'Manage inventory stock and movements', '1', '2026-06-10 14:05:15', '2026-06-10 14:05:15');

DROP TABLE IF EXISTS `sale_items`;
CREATE TABLE `sale_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sale_id` int(10) unsigned NOT NULL,
  `product_id` int(10) unsigned NOT NULL,
  `batch_id` int(10) unsigned NOT NULL,
  `quantity` int(10) unsigned NOT NULL,
  `unit_cost` decimal(12,2) NOT NULL,
  `unit_price` decimal(12,2) NOT NULL,
  `discount_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `line_total` decimal(14,2) NOT NULL,
  `line_cost` decimal(14,2) NOT NULL,
  `line_profit` decimal(14,2) NOT NULL,
  `product_snapshot` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Snapshot of product at time of sale' CHECK (json_valid(`product_snapshot`)),
  PRIMARY KEY (`id`),
  KEY `idx_sale` (`sale_id`),
  KEY `idx_product` (`product_id`),
  KEY `idx_batch` (`batch_id`),
  CONSTRAINT `sale_items_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sale_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `sale_items_ibfk_3` FOREIGN KEY (`batch_id`) REFERENCES `inventory_batches` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `batch_id`, `quantity`, `unit_cost`, `unit_price`, `discount_amount`, `line_total`, `line_cost`, `line_profit`, `product_snapshot`) VALUES
('1', '1', '8', '2', '13', '9.17', '15.00', '0.00', '195.00', '119.21', '75.79', '{\"id\":8,\"sku\":\"ACC-THERM\",\"name\":\"Digital Thermometer\",\"description\":\"High precision digital thermometer with hygrometer for incubator monitoring\",\"category\":\"Accessory\",\"capacity\":null,\"brand\":\"HatchPro\",\"model\":\"ACC-TH1\",\"unit\":\"unit\",\"low_stock_threshold\":10,\"is_active\":1,\"image_path\":null,\"created_by\":1,\"created_at\":\"2026-06-10 14:05:15\",\"updated_at\":\"2026-06-10 14:05:15\"}'),
('2', '2', '10', '3', '1', '23.00', '23.75', '0.00', '23.75', '23.00', '0.75', '{\"id\":10,\"sku\":\"ACC-TRNY\",\"name\":\"Automatic Turner Tray\",\"description\":\"Replacement automatic egg turner tray, compatible with most models\",\"category\":\"Accessory\",\"capacity\":null,\"brand\":\"HatchPro\",\"model\":\"ACC-TT1\",\"unit\":\"unit\",\"low_stock_threshold\":5,\"is_active\":1,\"image_path\":null,\"created_by\":1,\"created_at\":\"2026-06-10 14:05:15\",\"updated_at\":\"2026-06-10 14:05:15\"}'),
('3', '3', '10', '3', '1', '23.00', '23.75', '0.00', '23.75', '23.00', '0.75', '{\"id\":10,\"sku\":\"ACC-TRNY\",\"name\":\"Automatic Turner Tray\",\"description\":\"Replacement automatic egg turner tray, compatible with most models\",\"category\":\"Accessory\",\"capacity\":null,\"brand\":\"HatchPro\",\"model\":\"ACC-TT1\",\"unit\":\"unit\",\"low_stock_threshold\":5,\"is_active\":1,\"image_path\":null,\"created_by\":1,\"created_at\":\"2026-06-10 14:05:15\",\"updated_at\":\"2026-06-10 14:05:15\"}'),
('4', '4', '10', '3', '10', '23.00', '23.75', '0.00', '237.50', '230.00', '7.50', '{\"id\":10,\"sku\":\"ACC-TRNY\",\"name\":\"Automatic Turner Tray\",\"description\":\"Replacement automatic egg turner tray, compatible with most models\",\"category\":\"Accessory\",\"capacity\":null,\"brand\":\"HatchPro\",\"model\":\"ACC-TT1\",\"unit\":\"unit\",\"low_stock_threshold\":5,\"is_active\":1,\"image_path\":null,\"created_by\":1,\"created_at\":\"2026-06-10 14:05:15\",\"updated_at\":\"2026-06-10 14:05:15\"}'),
('5', '5', '5', '1', '2', '25.00', '35.50', '0.00', '71.00', '50.00', '21.00', '{\"id\":5,\"sku\":\"INC-176\",\"name\":\"176-Egg Incubator\",\"description\":\"High-capacity automatic incubator with solar backup compatibility\",\"category\":\"Industrial\",\"capacity\":176,\"brand\":\"AgroPro\",\"model\":\"AP-176S\",\"unit\":\"unit\",\"low_stock_threshold\":2,\"is_active\":1,\"image_path\":null,\"created_by\":1,\"created_at\":\"2026-06-10 14:05:15\",\"updated_at\":\"2026-06-10 14:05:15\"}'),
('6', '6', '8', '2', '7', '9.17', '15.00', '0.00', '105.00', '64.19', '40.81', '{\"id\":8,\"sku\":\"ACC-THERM\",\"name\":\"Digital Thermometer\",\"description\":\"High precision digital thermometer with hygrometer for incubator monitoring\",\"category\":\"Accessory\",\"capacity\":null,\"brand\":\"HatchPro\",\"model\":\"ACC-TH1\",\"unit\":\"unit\",\"low_stock_threshold\":10,\"is_active\":1,\"image_path\":null,\"created_by\":1,\"created_at\":\"2026-06-10 14:05:15\",\"updated_at\":\"2026-06-10 14:05:15\"}'),
('7', '7', '8', '2', '18', '9.17', '15.00', '0.00', '270.00', '165.06', '104.94', '{\"id\":8,\"sku\":\"ACC-THERM\",\"name\":\"Digital Thermometer\",\"description\":\"High precision digital thermometer with hygrometer for incubator monitoring\",\"category\":\"Accessory\",\"capacity\":null,\"brand\":\"HatchPro\",\"model\":\"ACC-TH1\",\"unit\":\"unit\",\"low_stock_threshold\":10,\"is_active\":1,\"image_path\":null,\"created_by\":1,\"created_at\":\"2026-06-10 14:05:15\",\"updated_at\":\"2026-06-10 14:05:15\"}'),
('8', '8', '2', '4', '2', '34.00', '55.00', '0.00', '110.00', '68.00', '42.00', '{\"id\":2,\"sku\":\"INC-56\",\"name\":\"56-Egg Incubator\",\"description\":\"Semi-automatic 56-egg incubator with humidity control\",\"category\":\"Semi-Auto\",\"capacity\":56,\"brand\":\"HatchPro\",\"model\":\"HP-56S\",\"unit\":\"unit\",\"low_stock_threshold\":3,\"is_active\":1,\"image_path\":null,\"created_by\":1,\"created_at\":\"2026-06-10 14:05:15\",\"updated_at\":\"2026-06-10 14:05:15\"}');

DROP TABLE IF EXISTS `sale_returns`;
CREATE TABLE `sale_returns` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `return_number` varchar(30) NOT NULL,
  `sale_id` int(10) unsigned NOT NULL,
  `return_date` date NOT NULL,
  `reason` text NOT NULL,
  `return_amount` decimal(14,2) NOT NULL,
  `restock` tinyint(1) NOT NULL DEFAULT 1,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'approved',
  `notes` text DEFAULT NULL,
  `processed_by` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `return_number` (`return_number`),
  KEY `processed_by` (`processed_by`),
  KEY `idx_sale` (`sale_id`),
  CONSTRAINT `sale_returns_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`),
  CONSTRAINT `sale_returns_ibfk_2` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `sale_returns` (`id`, `return_number`, `sale_id`, `return_date`, `reason`, `return_amount`, `restock`, `status`, `notes`, `processed_by`, `created_at`) VALUES
('1', 'RET-3B72C9BF', '7', '2026-06-10', 'some items expired', '90.00', '1', 'approved', NULL, '1', '2026-06-10 16:24:55');

DROP TABLE IF EXISTS `sales`;
CREATE TABLE `sales` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `invoice_number` varchar(30) NOT NULL,
  `customer_id` int(10) unsigned DEFAULT NULL,
  `customer_name` varchar(150) DEFAULT NULL COMMENT 'Snapshot for walk-in customers',
  `customer_phone` varchar(20) DEFAULT NULL,
  `sale_date` date NOT NULL,
  `subtotal` decimal(14,2) NOT NULL DEFAULT 0.00,
  `discount_type` enum('none','percent','fixed') NOT NULL DEFAULT 'none',
  `discount_value` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `tax_rate` decimal(5,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `total_cost` decimal(14,2) NOT NULL DEFAULT 0.00,
  `gross_profit` decimal(14,2) NOT NULL DEFAULT 0.00,
  `payment_method` enum('cash','ecocash','onemoney','bank_transfer','card','credit') NOT NULL DEFAULT 'cash',
  `payment_status` enum('paid','partial','unpaid','refunded') NOT NULL DEFAULT 'paid',
  `amount_paid` decimal(14,2) NOT NULL DEFAULT 0.00,
  `amount_due` decimal(14,2) NOT NULL DEFAULT 0.00,
  `status` enum('completed','returned','voided') NOT NULL DEFAULT 'completed',
  `notes` text DEFAULT NULL,
  `served_by` int(10) unsigned DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_number` (`invoice_number`),
  KEY `served_by` (`served_by`),
  KEY `created_by` (`created_by`),
  KEY `idx_invoice` (`invoice_number`),
  KEY `idx_customer` (`customer_id`),
  KEY `idx_sale_date` (`sale_date`),
  KEY `idx_status` (`status`),
  KEY `idx_payment_status` (`payment_status`),
  CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sales_ibfk_2` FOREIGN KEY (`served_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sales_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `sales` (`id`, `invoice_number`, `customer_id`, `customer_name`, `customer_phone`, `sale_date`, `subtotal`, `discount_type`, `discount_value`, `discount_amount`, `tax_rate`, `tax_amount`, `total_amount`, `total_cost`, `gross_profit`, `payment_method`, `payment_status`, `amount_paid`, `amount_due`, `status`, `notes`, `served_by`, `created_by`, `created_at`, `updated_at`) VALUES
('1', 'INV-202606-00001', NULL, NULL, NULL, '2026-06-10', '195.00', 'none', '0.00', '0.00', '0.00', '0.00', '195.00', '119.21', '75.79', 'cash', 'partial', '150.00', '45.00', 'completed', '', '3', '3', '2026-06-10 14:37:33', '2026-06-10 14:37:33'),
('2', 'INV-202606-00002', '1', NULL, NULL, '2026-06-10', '23.75', 'none', '0.00', '0.00', '0.00', '0.00', '23.75', '23.00', '0.75', 'cash', 'paid', '75.00', '0.00', 'completed', '', '3', '3', '2026-06-10 14:40:17', '2026-06-10 14:40:17'),
('3', 'INV-202606-00003', NULL, NULL, NULL, '2026-06-10', '23.75', 'none', '0.00', '0.00', '0.00', '0.00', '23.75', '23.00', '0.75', 'cash', 'paid', '261.75', '0.00', 'completed', '', '3', '3', '2026-06-10 14:45:28', '2026-06-10 14:45:28'),
('4', 'INV-202606-00004', '1', NULL, NULL, '2026-06-10', '237.50', 'none', '0.00', '0.00', '0.00', '0.00', '237.50', '230.00', '7.50', 'ecocash', 'paid', '237.50', '0.00', 'completed', '', '3', '3', '2026-06-10 14:46:59', '2026-06-10 14:46:59'),
('5', 'INV-202606-00005', '3', NULL, NULL, '2026-06-10', '71.00', 'none', '1.00', '0.00', '0.00', '0.00', '71.00', '50.00', '21.00', 'card', 'paid', '71.00', '0.00', 'completed', '', '3', '3', '2026-06-10 14:49:36', '2026-06-10 14:49:36'),
('6', 'INV-202606-00006', NULL, NULL, NULL, '2026-06-10', '105.00', 'none', '0.00', '0.00', '0.00', '0.00', '105.00', '64.19', '40.81', 'cash', 'paid', '105.00', '0.00', 'completed', '', '3', '3', '2026-06-10 14:51:26', '2026-06-10 14:51:26'),
('7', 'INV-202606-00007', '2', NULL, NULL, '2026-06-10', '270.00', 'none', '0.00', '0.00', '0.00', '0.00', '270.00', '165.06', '104.94', 'ecocash', 'paid', '270.00', '0.00', 'returned', '', '3', '3', '2026-06-10 14:53:02', '2026-06-10 16:24:55'),
('8', 'INV-202606-00008', '1', NULL, NULL, '2026-06-10', '110.00', 'none', '0.00', '0.00', '0.00', '0.00', '110.00', '68.00', '42.00', 'cash', 'partial', '109.97', '0.03', 'completed', '', '3', '3', '2026-06-10 16:57:15', '2026-06-10 16:57:15');

DROP TABLE IF EXISTS `stock_adjustments`;
CREATE TABLE `stock_adjustments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `adjustment_code` varchar(30) NOT NULL,
  `batch_id` int(10) unsigned NOT NULL,
  `product_id` int(10) unsigned NOT NULL,
  `adjustment_type` enum('add','remove','damage','correction') NOT NULL,
  `quantity_before` int(11) NOT NULL,
  `quantity_change` int(11) NOT NULL,
  `quantity_after` int(11) NOT NULL,
  `reason` text NOT NULL,
  `notes` text DEFAULT NULL,
  `adjusted_by` int(10) unsigned DEFAULT NULL,
  `adjusted_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `adjustment_code` (`adjustment_code`),
  KEY `product_id` (`product_id`),
  KEY `adjusted_by` (`adjusted_by`),
  KEY `idx_batch` (`batch_id`),
  KEY `idx_adjusted_at` (`adjusted_at`),
  CONSTRAINT `stock_adjustments_ibfk_1` FOREIGN KEY (`batch_id`) REFERENCES `inventory_batches` (`id`),
  CONSTRAINT `stock_adjustments_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `stock_adjustments_ibfk_3` FOREIGN KEY (`adjusted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `system_settings`;
CREATE TABLE `system_settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `data_type` enum('string','integer','decimal','boolean','json') NOT NULL DEFAULT 'string',
  `group_name` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `data_type`, `group_name`, `description`, `updated_by`, `updated_at`) VALUES
('1', 'company_name', 'Enterprise Incubator Systems', 'string', 'company', 'Company name', NULL, '2026-06-10 14:05:15'),
('2', 'company_address', 'Harare, Zimbabwe', 'string', 'company', 'Company address', NULL, '2026-06-10 14:05:15'),
('3', 'company_phone', '+263771000000', 'string', 'company', 'Company phone', NULL, '2026-06-10 14:05:15'),
('4', 'company_email', 'info@incubator.local', 'string', 'company', 'Company email', NULL, '2026-06-10 14:05:15'),
('5', 'currency_code', 'USD', 'string', 'finance', 'Currency code', NULL, '2026-06-10 14:05:15'),
('6', 'currency_symbol', '$', 'string', 'finance', 'Currency symbol', NULL, '2026-06-10 14:05:15'),
('7', 'tax_rate', '0.00', 'decimal', 'finance', 'Default tax rate (%)', NULL, '2026-06-10 14:05:15'),
('8', 'invoice_prefix', 'INV', 'string', 'sales', 'Invoice number prefix', NULL, '2026-06-10 14:05:15'),
('9', 'return_prefix', 'RET', 'string', 'sales', 'Return number prefix', NULL, '2026-06-10 14:05:15'),
('10', 'batch_prefix', 'BATCH', 'string', 'inventory', 'Batch code prefix', NULL, '2026-06-10 14:05:15'),
('11', 'adjustment_prefix', 'ADJ', 'string', 'inventory', 'Adjustment code prefix', NULL, '2026-06-10 14:05:15'),
('12', 'low_stock_alert', '5', 'integer', 'inventory', 'Global low stock threshold', NULL, '2026-06-10 14:05:15'),
('13', 'session_timeout', '1800', 'integer', 'security', 'Session timeout in seconds', NULL, '2026-06-10 14:05:15'),
('14', 'max_login_attempts', '5', 'integer', 'security', 'Max failed login attempts before lockout', NULL, '2026-06-10 14:05:15'),
('15', 'lockout_duration', '30', 'integer', 'security', 'Account lockout duration in minutes', NULL, '2026-06-10 14:05:15'),
('16', 'backup_enabled', '1', 'boolean', 'backup', 'Enable automatic backups', NULL, '2026-06-10 14:05:15'),
('17', 'backup_frequency', 'daily', 'string', 'backup', 'Backup frequency', NULL, '2026-06-10 14:05:15'),
('18', 'backup_retention', '30', 'integer', 'backup', 'Backup retention in days', NULL, '2026-06-10 14:05:15'),
('19', 'email_alerts', '0', 'boolean', 'notifications', 'Enable email alerts', NULL, '2026-06-10 14:05:15'),
('20', 'smtp_host', '', 'string', 'email', 'SMTP host', NULL, '2026-06-10 14:05:15'),
('21', 'smtp_port', '587', 'integer', 'email', 'SMTP port', NULL, '2026-06-10 14:05:15'),
('22', 'smtp_user', '', 'string', 'email', 'SMTP username', NULL, '2026-06-10 14:05:15'),
('23', 'smtp_pass', '', 'string', 'email', 'SMTP password (encrypted)', NULL, '2026-06-10 14:05:15'),
('24', 'items_per_page', '25', 'integer', 'ui', 'Default pagination size', NULL, '2026-06-10 14:05:15'),
('25', 'date_format', 'd/m/Y', 'string', 'ui', 'Date display format', NULL, '2026-06-10 14:05:15'),
('26', 'datetime_format', 'd/m/Y H:i', 'string', 'ui', 'DateTime display format', NULL, '2026-06-10 14:05:15');

DROP TABLE IF EXISTS `user_permissions`;
CREATE TABLE `user_permissions` (
  `user_id` int(10) unsigned NOT NULL,
  `permission_id` int(10) unsigned NOT NULL,
  `granted` tinyint(1) NOT NULL DEFAULT 1,
  `granted_at` datetime NOT NULL DEFAULT current_timestamp(),
  `granted_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`user_id`,`permission_id`),
  KEY `permission_id` (`permission_id`),
  CONSTRAINT `user_permissions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `user_sessions`;
CREATE TABLE `user_sessions` (
  `id` varchar(128) NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `device_type` varchar(50) DEFAULT NULL,
  `payload` text DEFAULT NULL,
  `last_active` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_expires` (`expires_at`),
  CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` int(10) unsigned NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_locked` tinyint(1) NOT NULL DEFAULT 0,
  `failed_attempts` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `locked_until` datetime DEFAULT NULL,
  `last_login_at` datetime DEFAULT NULL,
  `last_login_ip` varchar(45) DEFAULT NULL,
  `password_changed_at` datetime DEFAULT NULL,
  `must_change_password` tinyint(1) NOT NULL DEFAULT 0,
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_email` (`email`),
  KEY `idx_username` (`username`),
  KEY `idx_role` (`role_id`),
  KEY `idx_active` (`is_active`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `users` (`id`, `role_id`, `username`, `email`, `password_hash`, `full_name`, `phone`, `avatar`, `is_active`, `is_locked`, `failed_attempts`, `locked_until`, `last_login_at`, `last_login_ip`, `password_changed_at`, `must_change_password`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
('1', '1', 'admin', 'admin@incubator.local', '$argon2id$v=19$m=65536,t=4,p=1$TjZWZ2FDNzk4V1R4aEIxdQ$UgOIYx3V6KB+IoZ+By4qU2uFFMZ2DI2/EdnimM4uQe0', 'System Administrator', '+263771000000', NULL, '1', '0', '0', NULL, '2026-06-12 07:27:48', '::1', '2026-06-10 14:16:55', '0', NULL, '2026-06-10 14:05:15', '2026-06-12 07:27:48', NULL),
('2', '2', 'manager1', 'manager@incubator.local', '$argon2id$v=19$m=65536,t=4,p=1$TjZWZ2FDNzk4V1R4aEIxdQ$UgOIYx3V6KB+IoZ+By4qU2uFFMZ2DI2/EdnimM4uQe0', 'John Moyo', '+263772000001', NULL, '1', '0', '0', NULL, '2026-06-10 16:59:35', '::1', NULL, '0', '1', '2026-06-10 14:05:15', '2026-06-10 16:59:35', NULL),
('3', '3', 'sales1', 'sales@incubator.local', '$argon2id$v=19$m=65536,t=4,p=1$TjZWZ2FDNzk4V1R4aEIxdQ$UgOIYx3V6KB+IoZ+By4qU2uFFMZ2DI2/EdnimM4uQe0', 'Tendai Chikwanda', '+263773000002', NULL, '1', '0', '0', NULL, '2026-06-10 16:56:01', '::1', '2026-06-10 14:10:17', '0', '1', '2026-06-10 14:05:15', '2026-06-10 16:56:01', NULL),
('4', '4', 'clerk1', 'clerk@incubator.local', '$argon2id$v=19$m=65536,t=4,p=1$TjZWZ2FDNzk4V1R4aEIxdQ$UgOIYx3V6KB+IoZ+By4qU2uFFMZ2DI2/EdnimM4uQe0', 'Rudo Mupfudza', '+263774000003', NULL, '1', '0', '0', NULL, '2026-06-10 16:58:28', '::1', NULL, '0', '1', '2026-06-10 14:05:15', '2026-06-10 16:58:28', NULL);

SET FOREIGN_KEY_CHECKS = 1;

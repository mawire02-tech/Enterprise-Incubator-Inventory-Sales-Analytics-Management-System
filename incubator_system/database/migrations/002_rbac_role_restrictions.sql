-- ============================================================
-- Migration: Update Role Permissions
-- Apply this to existing databases instead of re-seeding.
-- ============================================================

USE incubator_system;

-- ─────────────────────────────────────────────────────────────
-- 1. MANAGER (role_id = 2)
--    Remove: ability to create/void/return sales, manage users,
--            view audit logs, access settings
-- ─────────────────────────────────────────────────────────────
DELETE FROM role_permissions
WHERE role_id = 2
  AND permission_id IN (
    SELECT id FROM permissions
    WHERE name IN (
        'sales.create',
        'sales.void',
        'sales.return',
        'sales.edit',
        'users.view',
        'users.create',
        'users.edit',
        'users.delete',
        'users.change_role',
        'users.unlock',
        'audit.view',
        'audit.export',
        'settings.view',
        'settings.edit',
        'settings.backup'
    )
);

-- ─────────────────────────────────────────────────────────────
-- 2. SALES OFFICER (role_id = 3)
--    Remove: reports.view (cannot access reports page at all)
--    Remove: sales.view_profit if it was granted
-- ─────────────────────────────────────────────────────────────
DELETE FROM role_permissions
WHERE role_id = 3
  AND permission_id IN (
    SELECT id FROM permissions
    WHERE name IN (
        'reports.view',
        'reports.generate',
        'reports.export',
        'reports.financial',
        'pnl.view',
        'sales.view_profit'
    )
);

-- Also remove any user-level overrides that would bypass the above
DELETE FROM user_permissions
WHERE granted = 1
  AND user_id IN (SELECT id FROM users WHERE role_id = 3)
  AND permission_id IN (
    SELECT id FROM permissions
    WHERE name IN (
        'reports.view', 'reports.generate', 'reports.export',
        'reports.financial', 'pnl.view', 'sales.view_profit'
    )
);

-- ─────────────────────────────────────────────────────────────
-- 3. STOCK CLERK (role_id = 4)
--    Remove: reports.view (cannot access reports)
-- ─────────────────────────────────────────────────────────────
DELETE FROM role_permissions
WHERE role_id = 4
  AND permission_id IN (
    SELECT id FROM permissions
    WHERE name IN (
        'reports.view',
        'reports.generate',
        'reports.export',
        'reports.financial',
        'pnl.view',
        'sales.view_profit'
    )
);

-- ─────────────────────────────────────────────────────────────
-- Verify final state
-- ─────────────────────────────────────────────────────────────
SELECT r.display_name AS role, p.name AS permission
FROM role_permissions rp
JOIN roles r ON r.id = rp.role_id
JOIN permissions p ON p.id = rp.permission_id
WHERE r.name IN ('manager','sales_officer','stock_clerk')
ORDER BY r.id, p.module, p.action;

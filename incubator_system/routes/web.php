<?php
/**
 * Application Routes
 */

use App\Core\Router;

$router = new Router();

// ─── Auth (guest only) ──────────────────────────────────────
$router->group(['middleware' => 'GuestMiddleware'], function (Router $r) {
    $r->get('/login',  'AuthController@showLogin');
    $r->post('/login', 'AuthController@login');
});

// ─── Auth (authenticated) ───────────────────────────────────
$router->group(['middleware' => 'AuthMiddleware'], function (Router $r) {

    $r->get('/logout', 'AuthController@logout');

    // Profile
    $r->get('/profile',                        'AuthController@showProfile');
    $r->post('/profile',                       'AuthController@updateProfile');
    $r->get('/profile/change-password',        'AuthController@showChangePassword');
    $r->post('/profile/change-password',       'AuthController@changePassword');

    // ─── Dashboard ───────────────────────────────────────
    $r->get('/',                               'DashboardController@index');
    $r->get('/dashboard',                      'DashboardController@index');
    $r->get('/dashboard/live-stats',           'DashboardController@liveStats');
    $r->get('/dashboard/activity-stream',      'DashboardController@activityStream');
    $r->get('/dashboard/chart-data',           'DashboardController@chartData');

    // ─── Products ────────────────────────────────────────
    $r->get('/inventory/products',             'InventoryController@products');
    $r->get('/inventory/products/create',      'InventoryController@createProduct');
    $r->post('/inventory/products/create',     'InventoryController@storeProduct');
    $r->get('/inventory/products/{id}/edit',   'InventoryController@editProduct');
    $r->post('/inventory/products/{id}/edit',  'InventoryController@updateProduct');

    // ─── Inventory Batches ───────────────────────────────
    $r->get('/inventory/batches',              'InventoryController@batches');
    $r->get('/inventory/batches/receive',      'InventoryController@receiveBatch');
    $r->post('/inventory/batches/receive',     'InventoryController@storeReceiveBatch');
    $r->get('/inventory/batches/{id}',         'InventoryController@batchDetail');
    $r->get('/inventory/batches/{id}/adjust',  'InventoryController@adjustStock');
    $r->post('/inventory/batches/{id}/adjust', 'InventoryController@storeAdjustment');
    $r->post('/inventory/batches/{id}/archive','InventoryController@archiveBatch');

    // Inventory AJAX
    $r->get('/inventory/stock-levels',         'InventoryController@stockLevels');
    $r->get('/inventory/movements',            'InventoryController@movements');

    // ─── Sales ───────────────────────────────────────────
    $r->get('/sales',                          'SalesController@index');
    $r->get('/sales/create',                   'SalesController@create');
    $r->post('/sales/create',                  'SalesController@store');
    $r->get('/sales/{id}',                     'SalesController@show');
    $r->get('/sales/{id}/print',               'SalesController@printInvoice');
    $r->post('/sales/{id}/void',               'SalesController@void');
    $r->get('/sales/{id}/return',              'SalesController@showReturn');
    $r->post('/sales/{id}/return',             'SalesController@storeReturn');

    // Sales AJAX
    $r->get('/sales/recent',                   'SalesController@recent');
    $r->get('/sales/daily-summary',            'SalesController@dailySummary');
    $r->get('/inventory/products/{id}/batches','SalesController@getProductBatches');

    // ─── Customers ───────────────────────────────────────
    $r->get('/customers',                      'CustomerController@index');
    $r->get('/customers/create',               'CustomerController@create');
    $r->post('/customers/create',              'CustomerController@store');
    $r->get('/customers/search',               'CustomerController@search');
    $r->get('/customers/{id}',                 'CustomerController@show');
    $r->get('/customers/{id}/edit',            'CustomerController@edit');
    $r->post('/customers/{id}/edit',           'CustomerController@update');
    $r->post('/customers/{id}/delete',         'CustomerController@destroy');

    // ─── Reports ─────────────────────────────────────────
    $r->get('/reports',                        'ReportsController@index');
    $r->get('/reports/sales',                  'ReportsController@sales');
    $r->get('/reports/inventory',              'ReportsController@inventory');
    $r->get('/reports/pnl',                    'ReportsController@pnl');
    $r->get('/reports/ledger',                 'ReportsController@ledger');
    $r->get('/reports/export',                 'ReportsController@export');
    $r->get('/reports/download',               'ReportsController@download');

    // ─── P&L ─────────────────────────────────────────────
    $r->get('/pnl',                            'PnLController@index');
    $r->get('/pnl/api',                        'PnLController@api');

    // ─── Users ───────────────────────────────────────────
    $r->get('/users',                          'UserController@index');
    $r->get('/users/create',                   'UserController@create');
    $r->post('/users/create',                  'UserController@store');
    $r->get('/users/{id}/edit',                'UserController@edit');
    $r->post('/users/{id}/edit',               'UserController@update');
    $r->post('/users/{id}/reset-password',     'UserController@resetPassword');
    $r->post('/users/{id}/unlock',             'UserController@unlock');
    $r->post('/users/{id}/delete',             'UserController@destroy');

    // ─── Audit ───────────────────────────────────────────
    $r->get('/audit',                          'AuditController@index');
    $r->get('/audit/{id}',                     'AuditController@show');
    $r->get('/audit/export',                   'AuditController@export');
    $r->get('/audit/feed',                     'AuditController@feed');

    // ─── Notifications ───────────────────────────────────
    $r->get('/notifications',                  'NotificationController@index');
    $r->get('/notifications/unread',           'NotificationController@unread');
    $r->post('/notifications/{id}/read',       'NotificationController@markRead');
    $r->post('/notifications/read-all',        'NotificationController@markAllRead');

    // ─── Calculator ──────────────────────────────────────
    $r->get('/calculator',                     'CalculatorController@index');
    $r->post('/calculator/calculate',          'CalculatorController@calculate');

    // ─── Settings ─────────────────────────────────────────────
    $r->get('/settings',                       'SettingsController@index');
    $r->post('/settings',                      'SettingsController@update');
    $r->post('/settings/backup',               'SettingsController@runBackup');
    $r->get('/settings/backup/{id}/download',  'SettingsController@downloadBackup');
});

return $router;

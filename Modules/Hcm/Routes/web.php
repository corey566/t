
<?php

Route::middleware('web', 'SetSessionData', 'auth', 'language', 'timezone', 'AdminSidebarMenu')->prefix('hcm')->group(function () {
    Route::get('/install', [Modules\Hcm\Http\Controllers\InstallController::class, 'index']);
    Route::post('/install', [Modules\Hcm\Http\Controllers\InstallController::class, 'install']);
    Route::get('/install/update', [Modules\Hcm\Http\Controllers\InstallController::class, 'update']);
    Route::get('/install/uninstall', [Modules\Hcm\Http\Controllers\InstallController::class, 'uninstall']);

    Route::get('/', [Modules\Hcm\Http\Controllers\HcmController::class, 'index']);
    Route::get('/tenant-config', [Modules\Hcm\Http\Controllers\HcmController::class, 'tenantConfig']);
    Route::post('/update-tenant-config', [Modules\Hcm\Http\Controllers\HcmController::class, 'updateTenantConfig']);
    Route::get('/test-connection', [Modules\Hcm\Http\Controllers\HcmController::class, 'testConnection']);
    Route::get('/sync-invoices', [Modules\Hcm\Http\Controllers\HcmController::class, 'syncInvoices']);
    Route::get('/synced-invoices', [Modules\Hcm\Http\Controllers\HcmController::class, 'syncedInvoices']);
    Route::get('/reports', [Modules\Hcm\Http\Controllers\HcmController::class, 'reports']);
    Route::post('/generate-report', [Modules\Hcm\Http\Controllers\HcmController::class, 'generateReport']);
    Route::get('/ping-monitor', [Modules\Hcm\Http\Controllers\HcmController::class, 'pingMonitor']);
    Route::get('/get-sync-log', [Modules\Hcm\Http\Controllers\HcmController::class, 'getSyncLog']);
    Route::get('/view-sync-log', [Modules\Hcm\Http\Controllers\HcmController::class, 'viewSyncLog']);
    Route::get('/get-log-details/{id}', [Modules\Hcm\Http\Controllers\HcmController::class, 'getLogDetails']);
    Route::get('/retry-failed-invoice/{id}', [Modules\Hcm\Http\Controllers\HcmController::class, 'retryFailedInvoice']);
});

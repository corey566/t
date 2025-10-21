
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
<?php

Route::middleware('web', 'SetSessionData', 'auth', 'language', 'timezone', 'AdminSidebarMenu')->prefix('hcm')->name('hcm.')->group(function () {
    Route::get('dashboard', [\Modules\Hcm\Http\Controllers\HcmController::class, 'dashboard'])->name('dashboard');
    Route::get('credentials', [\Modules\Hcm\Http\Controllers\HcmController::class, 'credentials'])->name('credentials');
    Route::post('save-credentials/{location_id}', [\Modules\Hcm\Http\Controllers\HcmController::class, 'saveCredentials'])->name('save.credentials');
    Route::delete('delete-credentials/{location_id}', [\Modules\Hcm\Http\Controllers\HcmController::class, 'deleteCredentials'])->name('delete.credentials');
    Route::post('test-api', [\Modules\Hcm\Http\Controllers\HcmTestController::class, 'testHcmApi'])->name('test.api');
    Route::post('test-all-invoice-types', [\Modules\Hcm\Http\Controllers\HcmTestController::class, 'testAllInvoiceTypes'])->name('test.all.invoices');
    Route::get('location/{location_id}/invoice-history', [\Modules\Hcm\Http\Controllers\HcmController::class, 'viewInvoiceHistory'])->name('invoice.history');
    Route::get('location/{location_id}/ping-monitor', [\Modules\Hcm\Http\Controllers\HcmController::class, 'showPingMonitor'])->name('ping.monitor');
    Route::post('location/{location_id}/ping', [\Modules\Hcm\Http\Controllers\HcmController::class, 'sendPing'])->name('ping');
    Route::get('location/{location_id}/ping-logs', [\Modules\Hcm\Http\Controllers\HcmController::class, 'getPingLogs'])->name('ping.logs');
    Route::post('location/{location_id}/test-connection', [\Modules\Hcm\Http\Controllers\HcmController::class, 'testConnection'])->name('test.connection');
    Route::post('location/{location_id}/sync-sales', [\Modules\Hcm\Http\Controllers\HcmController::class, 'syncSales'])->name('sync.sales');
    Route::get('location/{location_id}/download-excel', [\Modules\Hcm\Http\Controllers\HcmController::class, 'downloadExcel'])->name('download.excel');
    Route::post('location/{location_id}/upload-excel', [\Modules\Hcm\Http\Controllers\HcmController::class, 'uploadExcel'])->name('upload.excel');
    Route::get('manual-sync/{location_id}', [\Modules\Hcm\Http\Controllers\HcmController::class, 'manualSyncTest'])->name('manual.sync.test');
});

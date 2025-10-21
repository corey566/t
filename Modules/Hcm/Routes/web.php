<?php

Route::middleware('web', 'SetSessionData', 'auth', 'language', 'timezone', 'AdminSidebarMenu')->prefix('hcm')->name('hcm.')->group(function () {
    // Dashboard and credentials
    Route::get('credentials', [\Modules\Hcm\Http\Controllers\HcmController::class, 'credentials'])->name('credentials');
    Route::post('save-credentials/{location_id}', [\Modules\Hcm\Http\Controllers\HcmController::class, 'saveCredentials'])->name('save.credentials');
    Route::delete('delete-credentials/{location_id}', [\Modules\Hcm\Http\Controllers\HcmController::class, 'deleteCredentials'])->name('delete.credentials');

    // Location-specific routes
    Route::prefix('location/{location_id}')->group(function () {
        Route::get('invoice-history', [\Modules\Hcm\Http\Controllers\HcmController::class, 'viewInvoiceHistory'])->name('invoice.history');
        Route::get('ping-monitor', [\Modules\Hcm\Http\Controllers\HcmController::class, 'showPingMonitor'])->name('ping.monitor');
        Route::post('ping', [\Modules\Hcm\Http\Controllers\HcmController::class, 'sendPing'])->name('ping');
        Route::get('ping-logs', [\Modules\Hcm\Http\Controllers\HcmController::class, 'getPingLogs'])->name('ping.logs');
        Route::post('test-connection', [\Modules\Hcm\Http\Controllers\HcmController::class, 'testConnection'])->name('test.connection');
        Route::post('sync-sales', [\Modules\Hcm\Http\Controllers\HcmController::class, 'syncSales'])->name('sync.sales');
    });
});
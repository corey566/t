
<?php

use Modules\Hcm\Http\Controllers\HcmController;

Route::get('dashboard', [HcmController::class, 'dashboard'])->name('hcm.dashboard');
Route::get('credentials', [HcmController::class, 'credentials'])->name('hcm.credentials');
Route::post('save-credentials/{location_id}', [HcmController::class, 'saveCredentials'])->name('hcm.save.credentials');
Route::delete('delete-credentials/{location_id}', [HcmController::class, 'deleteCredentials'])->name('hcm.delete.credentials');
Route::get('location/{location_id}/invoice-history', [HcmController::class, 'viewInvoiceHistory'])->name('hcm.invoice.history');
Route::get('location/{location_id}/ping-monitor', [HcmController::class, 'showPingMonitor'])->name('hcm.ping.monitor');
Route::post('location/{location_id}/ping', [HcmController::class, 'sendPing'])->name('hcm.ping');
Route::get('location/{location_id}/ping-logs', [HcmController::class, 'getPingLogs'])->name('hcm.ping.logs');
Route::post('location/{location_id}/test-connection', [HcmController::class, 'testConnection'])->name('hcm.test.connection');
Route::post('location/{location_id}/sync-sales', [HcmController::class, 'syncSales'])->name('hcm.sync.sales');
Route::get('location/{location_id}/download-excel', [HcmController::class, 'downloadExcel'])->name('hcm.download.excel');
Route::post('location/{location_id}/upload-excel', [HcmController::class, 'uploadExcel'])->name('hcm.upload.excel');
Route::get('manual-sync/{location_id}', [HcmController::class, 'manualSyncTest'])->name('hcm.manual.sync.test');

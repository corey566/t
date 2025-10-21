<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/* Route::prefix('gallface')->group(function() {
    Route::get('/', 'GallfaceController@index');
}); */

Route::middleware('web', 'SetSessionData', 'auth', 'language', 'timezone', 'AdminSidebarMenu')->prefix('gallface')->group(function () {

	Route::get('dashboard', [\Modules\Gallface\Http\Controllers\GallfaceController::class, 'dashboard']);
	Route::any('setting', [\Modules\Gallface\Http\Controllers\GallfaceController::class, 'setting']);

	// Gallface API Routes
	Route::prefix('api')->group(function () {
		Route::post('save', [\Modules\Gallface\Http\Controllers\GallfaceController::class, 'saveGallfaceApi']);
		Route::post('update/{id}', [\Modules\Gallface\Http\Controllers\GallfaceController::class, 'updateGallfaceApi']);
		Route::delete('delete/{id}', [\Modules\Gallface\Http\Controllers\GallfaceController::class, 'deleteGallfaceApi']);
		Route::post('location/{location_id}/test', [\Modules\Gallface\Http\Controllers\GallfaceController::class, 'testGallfaceConnection']);
		Route::post('location/{location_id}/sync', [\Modules\Gallface\Http\Controllers\GallfaceController::class, 'syncGallfaceSales']);
		Route::post('location/{location_id}/ping', [\Modules\Gallface\Http\Controllers\GallfaceController::class, 'sendGallfacePing']);
	});
    Route::get('/invoices', 'GallfaceController@getGallfaceInvoices');
	Route::get('location/{location_id}/invoice-history', [\Modules\Gallface\Http\Controllers\GallfaceController::class, 'viewGallfaceInvoiceHistory']);

	// HCM Integration Routes - Independent endpoints
	Route::prefix('hcm')->name('hcm.')->group(function () {
		Route::get('credentials', [\Modules\Gallface\Http\Controllers\GallfaceController::class, 'hcmCredentials'])->name('credentials');
		Route::post('save-credentials/{location_id}', [\Modules\Gallface\Http\Controllers\GallfaceController::class, 'saveHcmCredentials'])->name('save.credentials');
		Route::delete('delete-credentials/{location_id}', [\Modules\Gallface\Http\Controllers\GallfaceController::class, 'deleteHcmCredentials'])->name('delete.credentials');
		Route::post('test-api', [\Modules\Gallface\Http\Controllers\HcmTestController::class, 'testHcmApi'])->name('test.api');
		Route::post('test-all-invoice-types', [\Modules\Gallface\Http\Controllers\HcmTestController::class, 'testAllInvoiceTypes'])->name('test.all.invoices');
		Route::get('location/{location_id}/invoice-history', [\Modules\Gallface\Http\Controllers\HcmController::class, 'viewInvoiceHistory'])->name('invoice.history');
		Route::get('location/{location_id}/ping-monitor', [\Modules\Gallface\Http\Controllers\HcmController::class, 'showPingMonitor'])->name('ping.monitor');
		Route::post('location/{location_id}/ping', [\Modules\Gallface\Http\Controllers\HcmController::class, 'sendPing'])->name('ping');
		Route::get('location/{location_id}/ping-logs', [\Modules\Gallface\Http\Controllers\HcmController::class, 'getPingLogs'])->name('ping.logs');
		Route::post('location/{location_id}/test-connection', [\Modules\Gallface\Http\Controllers\HcmController::class, 'testConnection'])->name('test.connection');
		Route::post('location/{location_id}/sync-sales', [\Modules\Gallface\Http\Controllers\HcmController::class, 'syncSales'])->name('sync.sales');
		Route::get('location/{location_id}/download-excel', [\Modules\Gallface\Http\Controllers\HcmController::class, 'downloadExcel'])->name('download.excel');
		Route::post('location/{location_id}/upload-excel', [\Modules\Gallface\Http\Controllers\HcmController::class, 'uploadExcel'])->name('upload.excel');
		Route::get('manual-sync/{location_id}', [\Modules\Gallface\Http\Controllers\HcmController::class, 'manualSyncTest'])->name('manual.sync.test');
	});

	// One Gallface (MIMS) Integration Routes - Independent endpoints
	Route::prefix('gallface')->name('gallface.')->group(function () {
		Route::post('save-api/{location_id?}', [\Modules\Gallface\Http\Controllers\GallfaceController::class, 'saveGallfaceApi'])->name('save.api');
		Route::put('update-api/{id}', [\Modules\Gallface\Http\Controllers\GallfaceController::class, 'updateGallfaceApi'])->name('update.api');
		Route::delete('delete-api/{id}', [\Modules\Gallface\Http\Controllers\GallfaceController::class, 'deleteGallfaceApi'])->name('delete.api');
		Route::post('location/{location_id}/test-connection', [\Modules\Gallface\Http\Controllers\GallfaceController::class, 'testGallfaceConnection'])->name('test.connection');
		Route::post('location/{location_id}/ping', [\Modules\Gallface\Http\Controllers\GallfaceController::class, 'sendGallfacePing'])->name('ping');
		Route::post('location/{location_id}/sync-sales', [\Modules\Gallface\Http\Controllers\GallfaceController::class, 'syncGallfaceSales'])->name('sync.sales');
		Route::get('location/{location_id}/invoice-history', [\Modules\Gallface\Http\Controllers\GallfaceController::class, 'viewGallfaceInvoiceHistory'])->name('invoice.history');
		Route::get('invoices', [\Modules\Gallface\Http\Controllers\GallfaceController::class, 'getGallfaceInvoices'])->name('invoices');
	});

	// Integra/Colombo routes moved to ColomboCity module

	Route::get('/install', [Modules\Gallface\Http\Controllers\InstallController::class, 'index']);
    Route::post('/install', [Modules\Gallface\Http\Controllers\InstallController::class, 'install']);
    Route::get('/install/update', [Modules\Gallface\Http\Controllers\InstallController::class, 'update']);
    Route::get('/install/uninstall', [Modules\Gallface\Http\Controllers\InstallController::class, 'uninstall']);

    Route::get('/', 'GallfaceController@index');

    // Auto-sync monitor
    Route::get('/monitor', function() {
        return view('gallface::gallface.auto_sync_monitor');
    });
    Route::get('/monitor/status', 'GallfaceController@getMonitorStatus');
});
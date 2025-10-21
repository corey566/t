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

	Route::get('location/{location_id}/invoice-history', [\Modules\Gallface\Http\Controllers\GallfaceController::class, 'viewGallfaceInvoiceHistory'])->name('gallface.invoice.history');

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
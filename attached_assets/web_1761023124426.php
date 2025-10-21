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
	
	Route::get('/install', [Modules\Gallface\Http\Controllers\InstallController::class, 'index']);
    Route::post('/install', [Modules\Gallface\Http\Controllers\InstallController::class, 'install']);
    Route::get('/install/update', [Modules\Gallface\Http\Controllers\InstallController::class, 'update']);
    Route::get('/install/uninstall', [Modules\Gallface\Http\Controllers\InstallController::class, 'uninstall']);
	
    Route::get('/', 'GallfaceController@index');
});

<?php

Route::middleware('web', 'SetSessionData', 'auth', 'language', 'timezone', 'AdminSidebarMenu')->prefix('colombo')->name('colombo.')->group(function () {
    Route::get('dashboard', [\Modules\ColomboCity\Http\Controllers\ColomboCityController::class, 'dashboard'])->name('dashboard');
    Route::get('credentials', [\Modules\ColomboCity\Http\Controllers\ColomboController::class, 'credentials'])->name('credentials');
    Route::post('save-credentials/{location_id}', [\Modules\ColomboCity\Http\Controllers\ColomboController::class, 'saveCredentials'])->name('save.credentials');
    Route::delete('delete-credentials/{location_id}', [\Modules\ColomboCity\Http\Controllers\ColomboController::class, 'deleteCredentials'])->name('delete.credentials');
    Route::post('test-connection/{location_id}', [\Modules\ColomboCity\Http\Controllers\ColomboController::class, 'testConnection'])->name('test.connection');
    Route::get('sync-logs', [\Modules\ColomboCity\Http\Controllers\ColomboController::class, 'getSyncLogs'])->name('sync.logs');
    Route::get('configuration', [\Modules\ColomboCity\Http\Controllers\ColomboCityController::class, 'configuration'])->name('configuration');
    Route::post('save-location-mapping', [\Modules\ColomboCity\Http\Controllers\ColomboCityController::class, 'saveLocationMapping'])->name('save.location.mapping');
    Route::get('transactions', [\Modules\ColomboCity\Http\Controllers\ColomboCityController::class, 'getTransactions'])->name('transactions');
});

// Integra API Routes
Route::middleware('web', 'SetSessionData', 'auth', 'language', 'timezone', 'AdminSidebarMenu')->prefix('integra')->name('integra.')->group(function () {
    Route::get('credentials', [\Modules\ColomboCity\Http\Controllers\IntegraController::class, 'credentials'])->name('credentials');
    Route::post('save-credentials', [\Modules\ColomboCity\Http\Controllers\IntegraController::class, 'saveCredentials'])->name('save.credentials');
    Route::get('api-logs', [\Modules\ColomboCity\Http\Controllers\IntegraController::class, 'getApiLogs'])->name('api.logs');
});


<?php

Route::middleware('web', 'SetSessionData', 'auth', 'language', 'timezone', 'AdminSidebarMenu')->prefix('colombo-city')->name('colombocity.')->group(function () {
    // Dashboard
    Route::get('dashboard', [\Modules\ColomboCity\Http\Controllers\ColomboCityController::class, 'dashboard'])->name('dashboard');
    Route::get('configuration', [\Modules\ColomboCity\Http\Controllers\ColomboCityController::class, 'configuration'])->name('configuration');
    
    // Location mapping
    Route::post('location-mapping', [\Modules\ColomboCity\Http\Controllers\ColomboCityController::class, 'saveLocationMapping'])->name('save.location.mapping');
    
    // Transactions
    Route::get('transactions', [\Modules\ColomboCity\Http\Controllers\ColomboCityController::class, 'getTransactions'])->name('transactions');
});

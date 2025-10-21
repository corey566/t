
<?php

use Modules\GallfaceMims\Http\Controllers\GallfaceMimsController;

Route::get('dashboard', [GallfaceMimsController::class, 'dashboard'])->name('gallfacemims.dashboard');
Route::any('settings', [GallfaceMimsController::class, 'settings'])->name('gallfacemims.settings');

Route::prefix('api')->group(function () {
    Route::post('save', [GallfaceMimsController::class, 'saveApi']);
    Route::post('update/{id}', [GallfaceMimsController::class, 'updateApi']);
    Route::delete('delete/{id}', [GallfaceMimsController::class, 'deleteApi']);
    Route::post('location/{location_id}/test', [GallfaceMimsController::class, 'testConnection']);
    Route::post('location/{location_id}/sync', [GallfaceMimsController::class, 'syncSales']);
    Route::post('location/{location_id}/ping', [GallfaceMimsController::class, 'sendPing']);
});

Route::get('invoices', [GallfaceMimsController::class, 'getInvoices'])->name('gallfacemims.invoices');
Route::get('location/{location_id}/invoice-history', [GallfaceMimsController::class, 'viewInvoiceHistory'])->name('gallfacemims.invoice.history');

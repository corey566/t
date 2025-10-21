
<?php

use Modules\ColomboCenter\Http\Controllers\IntegraController;

Route::get('credentials', [IntegraController::class, 'credentials'])->name('colombocenter.credentials');
Route::post('save-credentials', [IntegraController::class, 'saveCredentials'])->name('colombocenter.save.credentials');
Route::get('api-logs', [IntegraController::class, 'getApiLogs'])->name('colombocenter.api.logs');

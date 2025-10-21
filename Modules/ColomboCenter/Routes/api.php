
<?php

use Modules\ColomboCenter\Http\Controllers\Api\IntegraApiController;

Route::prefix('v1/integra')->group(function () {
    Route::post('receive', [IntegraApiController::class, 'receiveSalesInvoice']);
    Route::match(['get', 'put', 'patch', 'delete'], 'receive', [IntegraApiController::class, 'handleUnsupportedMethod']);
});

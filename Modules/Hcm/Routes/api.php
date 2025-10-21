
<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/hcm', function (Request $request) {
    return $request->user();
});

Route::prefix('hcm')->group(function () {
    Route::post('/webhook/ping-status', [Modules\Hcm\Http\Controllers\HcmWebhookController::class, 'pingStatus']);
    Route::post('/manual-sync/{invoice_id}', [Modules\Hcm\Http\Controllers\HcmController::class, 'manualSyncInvoice']);
});

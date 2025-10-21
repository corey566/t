
<?php

use Illuminate\Http\Request;
use Modules\ColomboCity\Http\Controllers\Api\ColomboCityApiController;

Route::middleware('auth:api')->get('/colombocity', function (Request $request) {
    return $request->user();
});

// Colombo City Center API Routes
Route::prefix('colombo-city')->group(function () {
    Route::post('sales-data', [ColomboCityApiController::class, 'receiveSalesData']);
    Route::get('test-connection', [ColomboCityApiController::class, 'testConnection']);
    
    Route::middleware('auth:api')->group(function () {
        Route::get('transactions', [ColomboCityApiController::class, 'getTransactions']);
    });
});

// Integra API Routes
Route::prefix('v1/integra')->group(function () {
    Route::post('receive', [\Modules\ColomboCity\Http\Controllers\Api\IntegraApiController::class, 'receiveSalesInvoice']);
    Route::match(['get', 'put', 'patch', 'delete'], 'receive', [\Modules\ColomboCity\Http\Controllers\Api\IntegraApiController::class, 'handleUnsupportedMethod']);
});

// Location-specific API routes
Route::middleware('auth:api', 'timezone')->prefix('colombo/api')->group(function () {
    Route::get('business-locations', [\Modules\ColomboCity\Http\Controllers\Api\ColomboBusinessLocationController::class, 'index']);
    Route::get('business-locations/{id}', [\Modules\ColomboCity\Http\Controllers\Api\ColomboBusinessLocationController::class, 'show']);
    
    Route::prefix('location/{location_id}')->group(function () {
        Route::get('products', [\Modules\ColomboCity\Http\Controllers\Api\ColomboProductController::class, 'index']);
        Route::get('products/{id}', [\Modules\ColomboCity\Http\Controllers\Api\ColomboProductController::class, 'show']);
        
        Route::get('sells', [\Modules\ColomboCity\Http\Controllers\Api\ColomboSellController::class, 'index']);
        Route::post('sells', [\Modules\ColomboCity\Http\Controllers\Api\ColomboSellController::class, 'store']);
        Route::get('sells/{id}', [\Modules\ColomboCity\Http\Controllers\Api\ColomboSellController::class, 'show']);
        
        Route::get('contacts', [\Modules\ColomboCity\Http\Controllers\Api\ColomboContactController::class, 'index']);
        Route::get('contacts/{id}', [\Modules\ColomboCity\Http\Controllers\Api\ColomboContactController::class, 'show']);
        
        Route::post('sync-sales', [\Modules\ColomboCity\Http\Controllers\Api\ColomboSellController::class, 'syncSales']);
        Route::post('sync-products', [\Modules\ColomboCity\Http\Controllers\Api\ColomboProductController::class, 'syncProducts']);
    });
});

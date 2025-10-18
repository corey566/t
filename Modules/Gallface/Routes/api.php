<?php

use Illuminate\Http\Request;
use Modules\Gallface\Http\Controllers\Api\ColomboCityApiController;

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

Route::middleware('auth:api')->get('/gallface', function (Request $request) {
    return $request->user();
});

// Colombo City Center API Routes
Route::prefix('colombo-city')->group(function () {
    // Public endpoints (for external system integration)
    Route::post('sales-data', [ColomboCityApiController::class, 'receiveSalesData']);
    Route::get('test-connection', [ColomboCityApiController::class, 'testConnection']);
    
    // Protected endpoints (require authentication)
    Route::middleware('auth:api')->group(function () {
        Route::get('transactions', [ColomboCityApiController::class, 'getTransactions']);
    });
});

// Integra API Routes (v1)
Route::prefix('v1/integra')->group(function () {
    // POST endpoint for receiving sales invoices
    Route::post('receive', [\Modules\Gallface\Http\Controllers\Api\IntegraApiController::class, 'receiveSalesInvoice']);
    
    // Reject other HTTP methods
    Route::match(['get', 'put', 'patch', 'delete'], 'receive', [\Modules\Gallface\Http\Controllers\Api\IntegraApiController::class, 'handleUnsupportedMethod']);
});

/*
|--------------------------------------------------------------------------
| Colombo City Center API Routes
|--------------------------------------------------------------------------
| Location-specific API routes for Colombo City Center integration
*/
Route::middleware('auth:api', 'timezone')->prefix('colombo/api')->group(function () {
    
    // Business Location Management
    Route::get('business-locations', [Modules\Gallface\Http\Controllers\Api\ColomboBusinessLocationController::class, 'index']);
    Route::get('business-locations/{id}', [Modules\Gallface\Http\Controllers\Api\ColomboBusinessLocationController::class, 'show']);
    
    // Location-specific routes (all require location_id)
    Route::prefix('location/{location_id}')->group(function () {
        
        // Product Management
        Route::get('products', [Modules\Gallface\Http\Controllers\Api\ColomboProductController::class, 'index']);
        Route::get('products/{id}', [Modules\Gallface\Http\Controllers\Api\ColomboProductController::class, 'show']);
        
        // Sales/Transaction Management
        Route::get('sells', [Modules\Gallface\Http\Controllers\Api\ColomboSellController::class, 'index']);
        Route::post('sells', [Modules\Gallface\Http\Controllers\Api\ColomboSellController::class, 'store']);
        Route::get('sells/{id}', [Modules\Gallface\Http\Controllers\Api\ColomboSellController::class, 'show']);
        
        // Contact/Customer Management
        Route::get('contacts', [Modules\Gallface\Http\Controllers\Api\ColomboContactController::class, 'index']);
        Route::get('contacts/{id}', [Modules\Gallface\Http\Controllers\Api\ColomboContactController::class, 'show']);
        
        // Sync endpoints
        Route::post('sync-sales', [Modules\Gallface\Http\Controllers\Api\ColomboSellController::class, 'syncSales']);
        Route::post('sync-products', [Modules\Gallface\Http\Controllers\Api\ColomboProductController::class, 'syncProducts']);
    });
});
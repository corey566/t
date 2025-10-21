
<?php

use Illuminate\Http\Request;

Route::middleware('auth:api')->get('/hcm', function (Request $request) {
    return $request->user();
});

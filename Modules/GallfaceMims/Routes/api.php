
<?php

use Illuminate\Http\Request;

Route::middleware('auth:api')->get('/gallfacemims', function (Request $request) {
    return $request->user();
});

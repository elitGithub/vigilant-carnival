<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\NadlanApiController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::prefix('api')->group(function () {
    Route::any('/assets-deals', [NadlanApiController::class, 'apiGetAssetsAndDeals']);
    Route::any('/get-data-by-query', [NadlanApiController::class, 'apiGetDataByQuery']);
});

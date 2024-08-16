<?php

use App\Http\Controllers\NadlanApiController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('api')->group(function () {
   Route::any('/assets-deals', [NadlanApiController::class, 'apiGetAssetsAndDeals']);
   Route::any('/get-data-by-query', [NadlanApiController::class, 'apiGetDataByQuery']);
});

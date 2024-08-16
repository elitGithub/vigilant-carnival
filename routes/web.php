<?php

use App\Http\Controllers\NadlanApiController;
use Illuminate\Support\Facades\Route;

Route::get('/api/get-address', [NadlanApiController::class, 'getApiNadlanAddressFromNadlan']);
Route::post('/api/assets-deals', [NadlanApiController::class, 'apiGetAssetsAndDeals']);

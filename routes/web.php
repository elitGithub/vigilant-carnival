<?php

use App\Http\Controllers\NadlanApiController;
use Illuminate\Support\Facades\Route;

Route::post('/api/assets-deals', [NadlanApiController::class, 'apiGetAssetsAndDeals']);

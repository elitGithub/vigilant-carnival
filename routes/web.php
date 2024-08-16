<?php

use App\Http\Controllers\NadlanApiController;
use Illuminate\Support\Facades\Route;

Route::any('/api/assets-deals', [NadlanApiController::class, 'apiGetAssetsAndDeals']);

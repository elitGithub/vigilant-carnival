<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    Artisan::call('route:list');
    return view('welcome');
});

Route::get('/cache-clear', function () {
    Artisan::call('make:cache-table');
    Artisan::call('migrate');
    Artisan::call('cache:clear');
    Artisan::call('config:cache');
    Artisan::call('route:clear');
    Artisan::call('route:cache');
    return view('welcome');
});

Route::get('/install_api', function () {
    Artisan::call('install:api');
    Artisan::call('config:cache');
    Artisan::call('route:clear');
    Artisan::call('route:cache');
    return view('welcome');
});


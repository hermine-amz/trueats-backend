<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebRestaurantController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/scan/{qr_code}', [WebRestaurantController::class, 'scanRestaurant']);

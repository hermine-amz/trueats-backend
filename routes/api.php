<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\RestaurantController;
use App\Http\Controllers\AvisController;
use App\Http\Controllers\ExplorationController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/restaurants/qr/{qr_code}', [RestaurantController::class, 'getByQrCode']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/restaurants/{id}/verify-gps', [RestaurantController::class, 'verifyGps']);

    Route::post('/avis', [AvisController::class, 'store']);
    Route::post('/avis/{id}/signal', [AvisController::class, 'signal']);

    Route::post('/restaurants/{id}/explore', [ExplorationController::class, 'explore']);
    Route::get('/explorations', [ExplorationController::class, 'index']);
});

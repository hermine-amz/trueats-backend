<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\RestaurantController;
use App\Http\Controllers\AvisController;
use App\Http\Controllers\ExplorationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PlatController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/restaurants/qr/{qr_code}', [RestaurantController::class, 'getByQrCode']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Profile management
    Route::put('/user/profile', [ProfileController::class, 'update']);
    Route::delete('/user/profile', [ProfileController::class, 'destroy']);

    Route::post('/restaurants/{id}/verify-gps', [RestaurantController::class, 'verifyGps']);

    Route::post('/avis', [AvisController::class, 'store']);
    Route::post('/avis/{id}/signal', [AvisController::class, 'signal']);

    Route::post('/restaurants/{id}/explore', [ExplorationController::class, 'explore']);
    Route::get('/explorations', [ExplorationController::class, 'index']);

    // Manager only routes
    Route::middleware('role:gerant')->group(function () {
        Route::put('/restaurants/{id}', [RestaurantController::class, 'update']);
        Route::post('/plats', [PlatController::class, 'store']);
        Route::put('/plats/{id}', [PlatController::class, 'update']);
        Route::delete('/plats/{id}', [PlatController::class, 'destroy']);
    });

    // Admin only routes
    Route::middleware('role:admin')->group(function () {
        Route::patch('/admin/restaurants/{id}/valider', [AdminController::class, 'validerRestaurant']);
        Route::post('/admin/restaurants/{id}/bloquer', [AdminController::class, 'bloquerRestaurant']);
        Route::post('/admin/users/{id}/bloquer', [AdminController::class, 'bloquerUser']);
        Route::delete('/admin/users/{id}', [AdminController::class, 'destroyUser']);
    });
});

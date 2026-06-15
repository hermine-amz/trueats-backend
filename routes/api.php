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
use App\Http\Controllers\UploadController;
use App\Http\Controllers\CategoryController;

Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::get('/restaurants/qr/{qr_code}', [RestaurantController::class, 'getByQrCode']);
Route::get('/restaurants', [RestaurantController::class, 'index']);
Route::get('/restaurants/{id}', [RestaurantController::class, 'show']);
Route::get('/restaurants/{id}/avis', [AvisController::class, 'index']);
Route::get('/categories', [CategoryController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Profile management
    Route::put('/user/profile', [ProfileController::class, 'update']);
    Route::delete('/user/profile', [ProfileController::class, 'destroy']);

    Route::post('/restaurants/{id}/verify-gps', [RestaurantController::class, 'verifyGps']);

    Route::post('/avis', [AvisController::class, 'store'])->middleware('throttle:10,1');
    Route::post('/avis/{id}/signal', [AvisController::class, 'signal'])->middleware('throttle:5,1');
    Route::get('/avis', [AvisController::class, 'allReviews']);

    Route::post('/restaurants/{id}/explore', [ExplorationController::class, 'explore']);
    Route::get('/explorations', [ExplorationController::class, 'index']);

    // Registered clients can submit a restaurant. The account becomes "gerant"
    // only after an administrator validates the submitted restaurant.
    Route::middleware('role:client,gerant')->group(function () {
        Route::post('/upload/document', [UploadController::class, 'storeDocument']);
        Route::post('/restaurants', [RestaurantController::class, 'store']);
    });

    // Manager only routes
    Route::middleware('role:gerant')->group(function () {
        Route::post('/categories', [CategoryController::class, 'store']);
        Route::post('/upload/image', [UploadController::class, 'store']);
        Route::put('/restaurants/{id}', [RestaurantController::class, 'update']);
        Route::post('/plats', [PlatController::class, 'store']);
        Route::put('/plats/{id}', [PlatController::class, 'update']);
        Route::delete('/plats/{id}', [PlatController::class, 'destroy']);
    });

    // Admin only routes
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/demandes', [AdminController::class, 'demandes']);
        Route::patch('/admin/restaurants/{id}/valider', [AdminController::class, 'validerRestaurant']);
        Route::post('/admin/restaurants/{id}/bloquer', [AdminController::class, 'bloquerRestaurant']);
        Route::post('/admin/users/{id}/bloquer', [AdminController::class, 'bloquerUser']);
        Route::delete('/admin/users/{id}', [AdminController::class, 'destroyUser']);
        Route::get('/admin/users', [AdminController::class, 'allUsers']);
        Route::get('/admin/signalements', [AdminController::class, 'allSignalements']);
        Route::post('/admin/signalements/{id}/handle', [AdminController::class, 'handleSignalement']);
    });
});

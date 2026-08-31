<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\Api\SyncController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/me', [AuthController::class, 'me']);
    Route::put('/me', [AuthController::class, 'updateProfile']);

    Route::auto('/users', UsersController::class, ['name' => 'users']);

    // Offline sync endpoints
    Route::prefix('sync')->group(function () {
        Route::get('/products', [SyncController::class, 'pullProducts']);
        Route::get('/categories', [SyncController::class, 'pullCategories']);
        Route::get('/satuan', [SyncController::class, 'pullSatuan']);
        Route::get('/customers', [SyncController::class, 'pullCustomers']);
        Route::get('/status', [SyncController::class, 'syncStatus']);
        Route::post('/orders', [SyncController::class, 'pushOrders']);
    });
});

<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // Customer and Admin
    Route::middleware(RoleMiddleware::class . ':customer,admin')->group(function () {
        Route::get('/products', [ProductController::class, 'index']); //->middleware(['throttle:products']);
        Route::get('/product/{id}', [ProductController::class, 'show']);
        Route::post('/orders', [OrderController::class, 'store']);
        Route::get('/orders', [OrderController::class, 'index']);
    });

    // Admin only
    Route::middleware(RoleMiddleware::class . ':admin')->group(function () {
        Route::post('/products', [ProductController::class, 'store']);
        Route::get('/product/{id}/edit', [ProductController::class, 'show']);
        Route::put('/product/{id}', [ProductController::class, 'update']);
        Route::delete('/product/{id}', [ProductController::class, 'destroy']);
    });
});

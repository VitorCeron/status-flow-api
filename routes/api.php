<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

/**
 * Auth Routes
 */
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
    });
});

/**
 * Admin backoffice
 */
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // Admin routes go here
});

/**
 * Normal users
 */
Route::middleware(['auth:sanctum', 'role:user'])->group(function () {
    // User routes go here
});

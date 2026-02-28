<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Monitor\MonitorController;
use App\Http\Controllers\Monitor\MonitorStatsController;
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
    /**
     * Dashboard Routes
     */
    Route::get('dashboard', [DashboardController::class, 'summary']);

    /**
     * Monitor Routes
     */
    Route::prefix('monitors')->group(function () {
        Route::get('/', [MonitorController::class, 'index']);
        Route::post('/', [MonitorController::class, 'store']);
        Route::get('/{id}', [MonitorController::class, 'show']);
        Route::put('/{id}', [MonitorController::class, 'update']);
        Route::delete('/{id}', [MonitorController::class, 'destroy']);
        Route::get('/{id}/stats', MonitorStatsController::class);
    });
});

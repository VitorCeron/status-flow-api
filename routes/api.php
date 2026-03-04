<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Backoffice\Dashboard\BackofficeDashboardController;
use App\Http\Controllers\Backoffice\Users\BackofficeUsersController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Monitor\MonitorController;
use App\Http\Controllers\Monitor\MonitorStatsController;
use App\Http\Controllers\Profile\ProfileController;
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
        Route::post('change-password', [AuthController::class, 'changePassword']);
        Route::delete('account', [AuthController::class, 'deleteAccount']);
    });
});

/**
 * Profile Routes
 */
Route::middleware('auth:sanctum')->prefix('profile')->group(function () {
    Route::get('timezones', [ProfileController::class, 'timezones']);
    Route::patch('settings', [ProfileController::class, 'updateSettings']);
});

/**
 * Admin backoffice
 */
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    /**
     * Backoffice Dashboard Routes
     */
    Route::get('backoffice/dashboard', [BackofficeDashboardController::class, 'summary']);

    /**
     * Backoffice Users Routes
     */
    Route::get('backoffice/users', [BackofficeUsersController::class, 'index']);
    Route::get('backoffice/users/{id}', [BackofficeUsersController::class, 'show']);
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

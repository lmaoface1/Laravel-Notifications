<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\NotificationController;

// Public Auth Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

// Protected Routes (require Sanctum token)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me',      [AuthController::class, 'me']);

    // 👇 Notification routes
    Route::get('/notifications',          [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);

    // Admin only
    Route::middleware('role:admin')->group(function () {
        Route::get('users',         [UserController::class, 'index']);
        Route::post('users',        [UserController::class, 'store']);
        Route::delete('users/{id}', [UserController::class, 'destroy']);
    });

    // Admin or Editor
    Route::middleware('role:admin|editor')->group(function () {
        Route::get('users/{id}',  [UserController::class, 'show']);
        Route::put('users/{id}',  [UserController::class, 'update']);
    });
});
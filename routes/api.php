<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

// Public Auth Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

// Protected Routes (require Sanctum token)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me',      [AuthController::class, 'me']);

    // Admin only: view all users and create users
    Route::middleware('role:admin')->group(function () {
        Route::get('users',         [UserController::class, 'index']);   // GET /api/users
        Route::post('users',        [UserController::class, 'store']);   // POST /api/users
        Route::delete('users/{id}', [UserController::class, 'destroy']); // DELETE /api/users/{id}
    });

    // Admin or Editor: view and update a user
    Route::middleware('role:admin|editor')->group(function () {
        Route::get('users/{id}',  [UserController::class, 'show']);   // GET /api/users/{id}
        Route::put('users/{id}',  [UserController::class, 'update']); // PUT /api/users/{id}
    });
});
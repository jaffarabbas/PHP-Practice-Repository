<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;

// Authentication route (requires Sanctum setup)
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// ============================================
// MIDDLEWARE EXAMPLES
// ============================================

// Example 1: Route with single middleware
Route::get('/users', [UserController::class, 'getUsers'])
    ->middleware('log.request');

// Example 2: Route with multiple middleware
Route::get('/users/{id}', [UserController::class, 'getUserByID'])
    ->middleware(['log.request', 'api.key']);

// Example 3: Group routes with shared middleware
Route::middleware(['log.request'])->group(function () {
    Route::post('/users', [UserController::class, 'store']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
});

// Example 4: Protected routes (require API key)
Route::prefix('protected')->middleware('api.key')->group(function () {
    Route::get('/users', [UserController::class, 'getUsers']);
});

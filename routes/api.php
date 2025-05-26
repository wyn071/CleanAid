<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\AdminController;

// Public routes
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Dashboard
    Route::get('/dashboard/stats', [DashboardController::class, 'getStats']);
    Route::get('/dashboard/activity', [DashboardController::class, 'getRecentActivity']);

    // Data Upload
    Route::post('/upload', [UploadController::class, 'upload']);
    Route::post('/upload/validate', [UploadController::class, 'validateFile']);

    // Data Review
    Route::get('/review/flagged', [ReviewController::class, 'getFlaggedRecords']);
    Route::post('/review/resolve', [ReviewController::class, 'resolveRecord']);
    Route::post('/review/merge', [ReviewController::class, 'mergeDuplicates']);

    // Export
    Route::get('/export/cleansed', [ExportController::class, 'exportCleansedData']);
    Route::get('/export/flagged', [ExportController::class, 'exportFlaggedData']);

    // Admin only routes
    Route::middleware('admin')->group(function () {
        Route::get('/admin/users', [AdminController::class, 'getUsers']);
        Route::post('/admin/users', [AdminController::class, 'createUser']);
        Route::put('/admin/users/{id}', [AdminController::class, 'updateUser']);
        Route::delete('/admin/users/{id}', [AdminController::class, 'deleteUser']);
    });
}); 
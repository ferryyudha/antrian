<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\QueueValidationController;
use App\Http\Controllers\Api\QueueController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AttendanceController;

// User Routes
Route::post('/queue', [QueueController::class, 'takeQueue']);
Route::get('/queue-status/{locationId}', [QueueController::class, 'getStatus']);
Route::get('/locations', [QueueController::class, 'getLocations']);
Route::post('/reprint-qr', [QueueController::class, 'reprintQr']);

// Staff Routes
Route::post('/queue/scan-preview', [QueueValidationController::class, 'scanPreview']);
Route::post('/queue/verify', [QueueValidationController::class, 'verifyQueue']);
Route::post('/attendance', [AttendanceController::class, 'checkIn']);

// Admin Routes
Route::get('/admin/queues', [AdminController::class, 'getQueues']);
Route::post('/admin/queues/{id}/cancel', [AdminController::class, 'cancelQueue']);
Route::post('/admin/locations/{id}/quota', [AdminController::class, 'updateQuota']);

Route::post('/admin/locations', [AdminController::class, 'storeLocation']);
Route::put('/admin/locations/{id}', [AdminController::class, 'updateLocation']);
Route::delete('/admin/locations/{id}', [AdminController::class, 'deleteLocation']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

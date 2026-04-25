<?php

use App\Http\Controllers\AiModel\DetectionController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\User\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;




Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/detections', [DetectionController::class, 'storeUserDetection']);
    Route::get('/detections/{id?}', [DetectionController::class, 'getUserDetections']);
    Route::get('/profile', [UserController::class, 'getUserprofile']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/verify-password', [AuthController::class, 'verifyPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::post('/resend-otp', [AuthController::class, 'resendOtp']);

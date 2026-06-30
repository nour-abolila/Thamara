<?php

use App\Http\Controllers\AiModel\DetectionController;
use App\Http\Controllers\AiModel\DetectionProgressController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\Notification\NotificationController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\User\UserProfileController;
use App\Models\DetectionProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::prefix('auth')->controller(AuthController::class)->group(function () {

    Route::post('/register', 'register');

    Route::post('/login', 'login')->middleware('throttle:5,1');

    Route::post('/verify-otp', 'verifyOtp');

    Route::post('/resend-otp', 'resendOtp');

    Route::post('/forgot-password', 'forgotPassword');

    Route::post('/verify-password', 'verifyPassword');

    Route::post('/reset-password', 'resetPassword');
});



Route::prefix('auth')->middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
});



Route::middleware('auth:sanctum')->group(function () {

    Route::get('/notifications', [NotificationController::class, 'index']);
});



Route::middleware('auth:sanctum')->controller(DetectionController::class)->group(function () {

    Route::post('/detections', 'storeUserDetection');

    Route::get('/detections/{id?}', 'getUserDetections');

    Route::delete('/detections/{id}', 'deleteUserDetection');
});



Route::middleware('auth:sanctum')->controller(DetectionProgressController::class)->group(function () {

    Route::post('/detections/scans/{detection}', 'storeScan');

    Route::get('/detections/scans/{detection}', 'getScan');
});



Route::middleware('auth:sanctum')->controller(UserProfileController::class)->group(function () {

    Route::get('/user-profile', 'show');

    Route::patch('/user-profile', 'update');

    Route::delete('/user-profile', 'destroy');
});



Route::post('/auth/social-login', [SocialAuthController::class, 'socialLogin']);

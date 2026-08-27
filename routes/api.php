<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CustomerAuthController;

// =====================================================
// PUBLIC API
// =====================================================

Route::apiResource('products', ProductController::class);

Route::get('categories', [CategoryController::class, 'index']);

// =====================================================
// CUSTOMER AUTHENTICATION
// =====================================================

// Send email verification OTP
Route::post(
    '/customer/send-otp',
    [CustomerAuthController::class, 'sendOtp']
);

// Verify email verification OTP
Route::post(
    '/customer/verify-otp',
    [CustomerAuthController::class, 'verifyOtp']
);

// Resend email verification OTP
Route::post(
    '/customer/resend-otp',
    [CustomerAuthController::class, 'resendOtp']
);

// Create customer account
Route::post(
    '/customer/register',
    [CustomerAuthController::class, 'register']
);

// Login
Route::post(
    '/customer/login',
    [CustomerAuthController::class, 'login']
);

// =====================================================
// PROTECTED CUSTOMER ROUTES
// =====================================================

Route::middleware('auth:sanctum')->group(function () {

    Route::get(
        '/customer/me',
        [CustomerAuthController::class, 'me']
    );

    Route::put(
        '/customer/profile',
        [CustomerAuthController::class, 'updateProfile']
    );

    Route::post(
        '/customer/logout',
        [CustomerAuthController::class, 'logout']
    );
});
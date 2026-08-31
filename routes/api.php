<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CustomerAuthController;
use App\Http\Controllers\Api\DeliveryZoneController;
use App\Http\Controllers\Api\DeliveryController;
use App\Http\Controllers\Api\OrderController;

/*
|--------------------------------------------------------------------------
| PUBLIC API
|--------------------------------------------------------------------------
*/

Route::apiResource(
    'products',
    ProductController::class
);

Route::get(
    'categories',
    [CategoryController::class, 'index']
);

Route::get(
    'delivery-zones',
    [DeliveryZoneController::class, 'index']
);

Route::get(
    'delivery-zones/{deliveryZone}',
    [DeliveryZoneController::class, 'show']
);

Route::post(
    'delivery/calculate',
    [DeliveryController::class, 'calculate']
);


/*
|--------------------------------------------------------------------------
| CUSTOMER AUTHENTICATION
|--------------------------------------------------------------------------
*/

Route::post(
    'customer/send-otp',
    [CustomerAuthController::class, 'sendOtp']
);

Route::post(
    'customer/verify-otp',
    [CustomerAuthController::class, 'verifyOtp']
);

Route::post(
    'customer/resend-otp',
    [CustomerAuthController::class, 'resendOtp']
);

Route::post(
    'customer/register',
    [CustomerAuthController::class, 'register']
);

Route::post(
    'customer/login',
    [CustomerAuthController::class, 'login']
);


/*
|--------------------------------------------------------------------------
| PROTECTED CUSTOMER ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | CUSTOMER PROFILE
    |--------------------------------------------------------------------------
    */

    Route::get(
        'customer/me',
        [CustomerAuthController::class, 'me']
    );

    Route::put(
        'customer/profile',
        [CustomerAuthController::class, 'updateProfile']
    );

    Route::post(
        'customer/logout',
        [CustomerAuthController::class, 'logout']
    );


    /*
    |--------------------------------------------------------------------------
    | CUSTOMER ORDERS
    |--------------------------------------------------------------------------
    */

    Route::get(
        'orders',
        [OrderController::class, 'index']
    );

    Route::get(
        'orders/{id}',
        [OrderController::class, 'show']
    );

    Route::post(
        'orders',
        [OrderController::class, 'store']
    );


    /*
    |--------------------------------------------------------------------------
    | CUSTOMER ORDER PAYMENT
    |--------------------------------------------------------------------------
    */

    Route::post(
        'orders/{id}/payment',
        [OrderController::class, 'confirmPayment']
    );
});
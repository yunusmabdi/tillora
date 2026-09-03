<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CustomerAuthController;
use App\Http\Controllers\Api\DriverAuthController;
use App\Http\Controllers\Api\DeliveryZoneController;
use App\Http\Controllers\Api\DeliveryController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\RiderController;

/*
|--------------------------------------------------------------------------
| PUBLIC API
|--------------------------------------------------------------------------
*/

Route::apiResource('products', ProductController::class);

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
| DRIVER AUTHENTICATION
|--------------------------------------------------------------------------
*/

Route::post(
    'driver/login',
    [DriverAuthController::class, 'login']
);


/*
|--------------------------------------------------------------------------
| PROTECTED CUSTOMER ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware(\Illuminate\Auth\Middleware\Authenticate::class . ':sanctum')->group(function () {

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

    Route::get(
        'delivery-zones',
        [OrderController::class, 'deliveryZones']
    );
});


/*
|--------------------------------------------------------------------------
| DRIVER API
|--------------------------------------------------------------------------
*/

Route::middleware(\Illuminate\Auth\Middleware\Authenticate::class . ':sanctum')
    ->prefix('driver')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Driver Profile
        |--------------------------------------------------------------------------
        */

        Route::get(
            'me',
            [RiderController::class, 'me']
        );

        Route::post(
            'logout',
            [DriverAuthController::class, 'logout']
        );


        /*
        |--------------------------------------------------------------------------
        | Assigned Orders
        |--------------------------------------------------------------------------
        */

        Route::get(
            'orders',
            [RiderController::class, 'orders']
        );

        Route::get(
            'orders/{sale}',
            [RiderController::class, 'show']
        );


        /*
        |--------------------------------------------------------------------------
        | Delivery Workflow
        |--------------------------------------------------------------------------
        */

        Route::post(
            'orders/{sale}/pickup',
            [RiderController::class, 'pickup']
        );

        Route::post(
            'orders/{sale}/start-delivery',
            [RiderController::class, 'startDelivery']
        );

        Route::post(
            'orders/{sale}/deliver',
            [RiderController::class, 'deliver']
        );

        Route::get(
            'notifications',
            [RiderController::class, 'notifications']
        );

        Route::get(
            'notifications/unread',
            [RiderController::class, 'unreadNotifications']
        );
    });
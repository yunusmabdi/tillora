<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CustomerAuthController;

Route::apiResource('products', ProductController::class);

Route::get('categories', [CategoryController::class, 'index']);

Route::post('/customer/register', [CustomerAuthController::class, 'register']);

Route::post('/customer/login', [CustomerAuthController::class, 'login']);

Route::get('/customer/me', [CustomerAuthController::class, 'me']);

Route::post('/customer/logout', [CustomerAuthController::class, 'logout']);
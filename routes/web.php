<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\POS\AuthController;
use App\Http\Controllers\POS\SalesHistoryController;
use App\Http\Controllers\ReceiptController;

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| POS Authentication
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {

    Route::get('/pos/login', [AuthController::class, 'showLogin'])
        ->name('pos.login');

    Route::post('/pos/login', [AuthController::class, 'login'])
        ->name('pos.login.submit');
});

/*
|--------------------------------------------------------------------------
| Protected POS
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:Cashier|Manager|Admin'])->group(function () {

    Route::get('/pos', function () {
        return view('pos.index');
    })->name('pos');

    Route::get('/pos/history', [SalesHistoryController::class, 'index'])
        ->name('pos.history');

    Route::post('/pos/logout', [AuthController::class, 'logout'])
        ->name('pos.logout');
});

/*
|--------------------------------------------------------------------------
| Shared Receipt / Invoice
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::get('/receipt/{sale}', [ReceiptController::class, 'show'])
        ->name('receipt.show');

});

Route::post('/demo/cashier', [AuthController::class, 'demoCashier'])
    ->name('demo.cashier');

Route::post('/demo/admin', [AuthController::class, 'demoAdmin'])
    ->name('demo.admin');
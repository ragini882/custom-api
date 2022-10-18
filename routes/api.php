<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerAccountController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('v1')->group(function () {
    Route::post('register', [AuthController::class, 'registration']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('send-otp', [AuthController::class, 'sendOtp']);
        Route::post('verify-otp', [AuthController::class, 'verifyOtp']);
        Route::post('reset-password', [AuthController::class, 'resetPassword']);

        Route::post('create-user-account', [CustomerAccountController::class, 'createDwollaAccount']);
        Route::post('add-user-bank', [CustomerAccountController::class, 'addUserBank']);

        Route::post('get-link-token', [CustomerAccountController::class, 'getLinkToken']);
        Route::post('get-bank-list', [CustomerAccountController::class, 'getBankList']);

        Route::post('add-balance', [CustomerAccountController::class, 'addDwollaBalance']);
    });
});

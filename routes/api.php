<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;

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
    Route::post('register', [RegisterController::class, 'registration']);
    Route::post('login', [RegisterController::class, 'login']);
    Route::post('forgot-password', [RegisterController::class, 'forgotPassword']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('send-otp', [RegisterController::class, 'sendOtp']);
        Route::post('verify-otp', [RegisterController::class, 'verifyOtp']);
        Route::post('reset-password', [RegisterController::class, 'resetPassword']);
    });
});

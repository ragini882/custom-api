<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerAccountController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\DwollaWebhookController;
use App\Http\Controllers\RequestPaymentController;

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
        Route::post('profile-image', [CustomerAccountController::class, 'profileImage']);

        Route::post('get-user-detail', [CustomerAccountController::class, 'userDetail']);

        Route::post('create-user-account', [CustomerAccountController::class, 'createDwollaAccount']);
        Route::post('add-user-bank', [CustomerAccountController::class, 'addUserBank']);

        Route::post('get-link-token', [CustomerAccountController::class, 'getLinkToken']);
        Route::post('get-bank-list', [CustomerAccountController::class, 'getBankList']);

        Route::post('add-balance', [CustomerAccountController::class, 'addDwollaBalance']);
        Route::post('transaction-list', [CustomerAccountController::class, 'getTransactionList']);

        Route::post('customer-list', [CustomerAccountController::class, 'getCustomerList']);
        Route::post('withdraw-balance', [CustomerAccountController::class, 'withdrawBalance']);

        Route::post('group/create', [GroupController::class, 'createGroup']);
        Route::post('group/list', [GroupController::class, 'getGroupList']);
        Route::post('group/{id}/delete', [GroupController::class, 'deleteGroup']);
        Route::post('group/add-customer', [GroupController::class, 'addCustomerGroup']);
        Route::post('group/delete-customer', [GroupController::class, 'deleteCustomerGroup']);
        Route::post('group/contribute', [GroupController::class, 'contributeAmount']);
        Route::post('group/withdraw-amount', [GroupController::class, 'withdrawGroupAmount']);

        //Request
        Route::post('request-payment', [RequestPaymentController::class, 'requestPayment']);
        Route::post('accept-request', [RequestPaymentController::class, 'acceptRequest']);
        Route::post('reject-request', [RequestPaymentController::class, 'rejectRequest']);
        Route::post('get-request-payment', [RequestPaymentController::class, 'getRequestPayment']);
        Route::POST('getRate', [RequestPaymentController::class, 'fetchRate']);
        Route::post('transferVerifyToReceiveOnly', [RequestPaymentController::class, 'transferVerifyToReceiveOnly']);
        //scan QR code
        Route::post('scan-qr-transfer-amount', [ScanController::class, 'scanQrTransferAmount']);
    });
    Route::post('get-currency-account', [RequestPaymentController::class, 'convert']);
    Route::POST('createConversion', [RequestPaymentController::class, 'createConversion']);
    Route::POST('createBeneficiary', [RequestPaymentController::class, 'createBeneficiary']);
    Route::POST('createPayment', [RequestPaymentController::class, 'createPayment']);
    Route::POST('balance', [RequestPaymentController::class, 'getBalanceCurrencyCloud']);

    Route::post('webhook/dwolla-status', [DwollaWebhookController::class, 'webhookRequest']);
});

<?php

namespace App\Http\Controllers;

use App\Http\Requests\RequestPaymentRequest;
use App\Http\Requests\CreateConversionRequest;
use App\Http\Requests\RateRequest;
use App\Http\Requests\BeneficiaryRequest;
use App\Http\Requests\InternationalPaymentRequest;
use App\Models\User;
use App\Models\UserDwollaAccount;
use App\Models\PaymentRequest;
use App\Models\UserAccount;
use App\Traits\ResponseTrait;
use App\Traits\CurrencyCloudTrait;
use App\Traits\DwollaTrait;
use Illuminate\Support\Facades\Auth;

class RequestPaymentController extends Controller
{
    use ResponseTrait, CurrencyCloudTrait, DwollaTrait;
    private $token_name = 'USER_TOKEN';

    public function requestPayment(RequestPaymentRequest $request)
    {
        $auth_user = auth()->user();
        $request_data = [
            "from_account_id" => $auth_user->userAccount->id,
            "to_account_id" => $request->input("to_account_id"),
            "note" => $request->input("note"),
            "amount" => $request->input("amount")
        ];
        $request_payment = PaymentRequest::create($request_data);
        return $this->sendSuccessResponse('Request send successfully.', $request_payment);
    }

    public function getRequestPayment()
    {
        $auth_user = auth()->user();
        $request_payments = PaymentRequest::with('userAccountTo', 'userAccountFrom')->get();
        return $this->sendSuccessResponse('Request for Payment.', $request_payments);
    }

    public function fetchRate(RateRequest $request)
    {
        $user = auth()->user();
        $convertAmountRate = $this->rateDetail($request->all());
        return $this->sendSuccessResponse('Converted amount rate.', $convertAmountRate);
    }

    public function createConversion(CreateConversionRequest $request)
    {
        $user = auth()->user();
        $conversion = $this->createConversioneDetail($request->all());
        return $this->sendSuccessResponse('Converted amount.', $conversion);
    }

    public function createBeneficiary(BeneficiaryRequest $request)
    {
        $user = auth()->user();
        $beneficiary = $this->createBeneficiaryDetail($request->all());
        return $this->sendSuccessResponse('Beneficiary successfully created.', $beneficiary);
    }

    public function createPayment(InternationalPaymentRequest $request)
    {
        $user = auth()->user();
        $payment = $this->createPaymentDetail($request->all());
    }
}

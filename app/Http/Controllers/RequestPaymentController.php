<?php

namespace App\Http\Controllers;

use App\Http\Requests\RequestPaymentRequest;
use App\Http\Requests\CreateConversionRequest;
use App\Http\Requests\RateRequest;
use App\Http\Requests\BeneficiaryRequest;
use App\Http\Requests\AcceptRequest;
use App\Http\Requests\PaymentRequestDetail;
use App\Http\Requests\RejectRequest;
use App\Http\Requests\CCBalanceRequest;
use App\Http\Requests\WithdrawBalanceRequest;
use App\Models\User;
use App\Models\UserDwollaAccount;
use App\Models\PaymentRequest;
use App\Models\UserAccount;
use App\Models\Beneficiary;
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

    public function acceptRequest(AcceptRequest $request)
    {
        $auth_user = auth()->user();
        $accept = PaymentRequest::where("to_account_id", $request->input("sender_id"))
            ->orWhere("from_account_id", $auth_user->userAccount->id)->orWhere("id", $request->input("request_id"))->first();
        $accept->status = "ACCEPTED";
        return $this->sendSuccessResponse('Accept the Request.', $accept);
    }

    public function rejectRequest(RejectRequest $request)
    {
        $auth_user = auth()->user();
        $accept = PaymentRequest::where("to_account_id", $request->input("sender_id"))
            ->orWhere("from_account_id", $auth_user->userAccount->id)->orWhere("id", $request->input("request_id"))->first();
        $accept->status = "REJECTED";

        return $this->sendSuccessResponse('Rejected the Request.', $accept);
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

        Beneficiary::create([
            'cc_contact_uuid' => $request->input('on_behalf_of'),
            'beneficiary_uuid' => $beneficiary->id,
            'name' => $beneficiary->name,
            'bank_account_holder_name' => $beneficiary->bank_account_holder_name,
            'bank_country' => $beneficiary->bank_country,
            'currency' => $beneficiary->currency,
            'account_number' => $beneficiary->account_number,
            'routing_code_type_1' => $beneficiary->routing_code_type_1,
            'routing_code_value_1' => $beneficiary->routing_code_value_1,
            'iban' => $beneficiary->iban
        ]);
        return $this->sendSuccessResponse('Beneficiary successfully created.', $beneficiary);
    }

    public function getBalanceCurrencyCloud(CCBalanceRequest $request)
    {
        $user = auth()->user();
        $payment = $this->BalanceCurrencyCloud($request->all());
        return $this->sendSuccessResponse('Balance get.', $payment);
    }

    public function createPayment(PaymentRequestDetail $request)
    {
        $user = auth()->user();
        $payment = $this->createPaymentDetail($request->all());
        return $this->sendSuccessResponse('Payment successfully created.', $payment);
    }

    public function transferVerifyToReceiveOnly(WithdrawBalanceRequest $request)
    {
        $auth_user = auth()->user();
        $this->transferVerifyBankToReceiveOnlyBank($auth_user->userAccount, $request->all());
        return $this->sendSuccessResponse('Balance transfer to user successfully.');
    }
}

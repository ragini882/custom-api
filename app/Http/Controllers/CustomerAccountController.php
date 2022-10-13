<?php

namespace App\Http\Controllers;

use App\Http\Requests\DwollaAccountRequest;
use App\Http\Requests\AddBankRequest;
use App\Models\User;
use App\Models\UserAccount;
use App\Models\UserBank;
use App\Traits\ResponseTrait;
use App\Traits\DwollaTrait;
use App\Traits\PlaidTrait;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Log;

class CustomerAccountController extends Controller
{
    use ResponseTrait, DwollaTrait, PlaidTrait;

    public function createDwollaAccount(DwollaAccountRequest $request)
    {
        $auth_user = auth()->user();
        if (!$auth_user->is_account_verified) {
            $user = [
                "firstName" => $request->input('legal_first_name'),
                "lastName" => $request->input('legal_last_name'),
                "email" => $auth_user->email,
                "type" => strtolower($auth_user->account_type),
                "address1" => $request->input('street_address'),
                "address2" => $request->input('address_type'),
                "city" => $request->input('city'),
                "state" => $request->input('state'),
                "postalCode" => $request->input('zip_code'),
                "dateOfBirth" => date("Y-m-d", strtotime($request->input('dob'))),
                "ssn" => $request->input('ssn')
            ];

            $customer = $this->createCustomer($user);
            $user_dwolla_account = new UserAccount;
            $user_dwolla_account->user_id = $auth_user->id;
            $user_dwolla_account->customer_uuid = $customer->uuid;
            $user_dwolla_account->legal_first_name = $user['firstName'];
            $user_dwolla_account->legal_last_name = $user['lastName'];
            $user_dwolla_account->dob = $user['dateOfBirth'];
            $user_dwolla_account->ssn = $user['ssn'];
            $user_dwolla_account->street_address = $user['address1'];
            $user_dwolla_account->address_type = $user['address2'];
            $user_dwolla_account->city = $user['city'];
            $user_dwolla_account->state = $user['state'];
            $user_dwolla_account->zip_code = $user['postalCode'];
            $user_dwolla_account->save();

            $user = User::where('id', $auth_user->id)->first();
            $user->is_account_verified = 1;
            $user->save();
            $user['user_account'] = $user_dwolla_account;
            return $this->sendSuccessResponse('Account verified successfully.', $user);
        } else {
            return $this->sendBadRequestResponse('Account is already verified');
        }
    }

    public function addUserBank(AddBankRequest $request)
    {
        $auth_user = auth()->user();
        if (is_null($auth_user->userAccount)) {
            return $this->sendBadRequestResponse('Account is not verified for this user.');
        }
        if (strtoupper($request->input("bank_verify_type")) == 'MANUAL') {
            $bank_data = [
                "routingNumber" => $request->input("routing_number"),
                "accountNumber" => $request->input("account_number"),
                "bankAccountType" => "checking",
                "name" => $auth_user->first_name
            ];
            $bank = $this->addBank($bank_data, $auth_user->userAccount->customer_uuid, true);
            $user_bank = new UserBank;
            $user_bank->user_account_id = $auth_user->userAccount->id;
            $user_bank->funding_source_uuid = $bank->uuid;
            $user_bank->routing_number = $bank_data['routingNumber'];
            $user_bank->account_number = $bank_data['accountNumber'];
            $user_bank->bank_account_type = $bank_data['bankAccountType'];
            $user_bank->save();
        } else {
            $access_data = $this->createAccessToken($request->input("public_token"));
            $processor_data = $this->createProcessorToken($access_data->access_token, $request->input("account_id"));
            $bank_data = [
                "plaidToken" => $processor_data->processor_token,
                "bankAccountType" => "checking",
                "name" => $auth_user->first_name
            ];
            $bank = $this->addBank($bank_data, $auth_user->userAccount->customer_uuid, false);
            $user_bank = new UserBank;
            $user_bank->user_account_id = $auth_user->userAccount->id;
            $user_bank->funding_source_uuid = $bank->uuid;
            $user_bank->plaid_token = $bank_data['plaidToken'];
            $user_bank->bank_account_type = $bank_data['bankAccountType'];
            $user_bank->save();
        }
        return $this->sendSuccessResponse('Bank has been added successfully.', $user_bank);
    }

    public function getLinkToken()
    {
        $auth_user = auth()->user();
        $linkToken = $this->createLinkToken($auth_user->userAccount);
        return $this->sendSuccessResponse('Link token has been generated successfully.', $linkToken);
    }
}

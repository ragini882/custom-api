<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Exception;

trait CurrencyCloudTrait
{
    private $x_auth_token;
    private function logIn()
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json'
        ])->post(config('app.currency.url') . '/authenticate/api', [
            'login_id' => config('app.currency.login_id'),
            'api_key' => config('app.currency.api_key')
        ]);
        if ($response->successful()) {
            $this->x_auth_token = $response->object()->auth_token;
        } else {
            throw new Exception(json_encode($response->object()->error_messages));
        }
    }

    private function logOut()
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'X-Auth-Token' => $this->x_auth_token
        ])->post(config('app.currency.url') . '/authenticate/close_session');
        if ($response->successful()) {
            return $response->object();
        } else {
            throw new Exception(json_encode($response->object()->error_messages));
        }
    }

    public function convertAmount($covert_currency)
    {
        $this->logIn();
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'X-Auth-Token' => $this->x_auth_token
        ])->get(config('app.currency.url') . '/rates/detailed', [
            'buy_currency' => $covert_currency['buy_currency'],
            'sell_currency' => $covert_currency['sell_currency'],
            'fixed_side' => $covert_currency['fixed_side'],
            'amount' => $covert_currency['amount'],
            'conversion_date_preference' => $covert_currency['conversion_date_preference']
        ]);
        $this->logOut();
        if ($response->successful()) {
            return $response->object();
        } else {
            throw new Exception(json_encode($response->object()->error_messages));
        }
    }

    public function createSubAccount($customer)
    {
        //dd($customer);
        $this->logIn();
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'X-Auth-Token' => $this->x_auth_token
        ])->post(config('app.currency.url') . '/accounts/create', [
            'account_name' => $customer['firstName'],
            'legal_entity_type' => 'company',
            'street' => $customer['address1'],
            'city' => $customer['city'],
            'country' => 'US',
            'postal_code' => $customer['postalCode'],
            'your_reference' => '12345678',
            'status' => 'enabled',
            'state_or_province' => 'CO',
            'identification_type' => 'incorporation_number',
            'identification_value' => '123456789'
        ]);
        $this->logOut();
        if ($response->successful()) {
            return $response->object();
        } else {
            throw new Exception(json_encode($response->object()->error_messages));
        }
    }

    public function contactSubAccount($subAccountDetail, $user, $auth_user)
    {
        $this->logIn();
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'X-Auth-Token' => $this->x_auth_token
        ])->post(config('app.currency.url') . '/contacts/create', [
            'account_id' => $subAccountDetail->id,
            'first_name' => $user['firstName'],
            'last_name' => $user['lastName'],
            'email_address' => str_replace("@", "+1@", $user['email']),
            'phone_number' => '1234567890',
            'your_reference' => '123456789',
            'status' => 'enabled',
            'timezone' => 'America/New York',
            'date_of_birth' => date("Y-m-d", strtotime($user['dateOfBirth']))
        ]);
        $this->logOut();
        if ($response->successful()) {
            return $response->object();
        } else {
            throw new Exception(json_encode($response->object()->error_messages));
        }
    }

    public function createConversionBehalf()
    {
        $this->logIn();
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'X-Auth-Token' => $this->x_auth_token
        ])->post(config('app.currency.url') . '/conversions/create', [
            'buy_currency' => 'EUR',
            'sell_currency' => 'GBP',
            'amount' => '10.00',
            'fixed_side' => 'buy',
            'reason' => 'food',
            'term_agreement' => 'true',
            'on_behalf_of' => '167bd354-7779-43da-afc1-648d5064bdc5'
        ]);
        $this->logOut();
        if ($response->successful()) {
            // dd($response->object());
            return $response->object();
        } else {
            throw new Exception(json_encode($response->object()->error_messages));
        }
    }

    public function findFundingSource($contact_id, $account_id)
    {
        $this->logIn();
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'X-Auth-Token' => $this->x_auth_token
        ])->get(config('app.currency.url') . '/funding_accounts/find', [
            'payment_type' => 'regular',
            'currency' => 'USD',
            'account_id' => $account_id,
            'on_behalf_of' => $contact_id
        ]);
        // dd($response);
        $this->logOut();
        if ($response->successful()) {
            //dd($response->object());
            return $response->object();
        } else {
            throw new Exception(json_encode($response->object()->error_messages));
        }
    }

    public function rateDetail($rate_detail)
    {
        $this->logIn();
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'X-Auth-Token' => $this->x_auth_token
        ])->get(config('app.currency.url') . '/rates/detailed', [
            'buy_currency' => $rate_detail['buy_currency'],
            'sell_currency' => $rate_detail['sell_currency'],
            'fixed_side' => 'sell',
            'amount' => $rate_detail['amount'],
            'on_behalf_of' => $rate_detail['on_behalf_of']
        ]);
        // dd($response);
        $this->logOut();
        if ($response->successful()) {
            //dd($response->object());
            return $response->object();
        } else {
            throw new Exception(json_encode($response->object()->error_messages));
        }
    }

    public function createConversioneDetail($conversion)
    {
        $this->logIn();
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'X-Auth-Token' => $this->x_auth_token
        ])->post(config('app.currency.url') . '/conversions/create', [
            'buy_currency' => $conversion['buy_currency'],
            'sell_currency' => $conversion['sell_currency'],
            'fixed_side' => $conversion['fixed_side'],
            'amount' => $conversion['amount'],
            'term_agreement' => 'true',
            'reason' => $conversion['reason'],
            'on_behalf_of' => $conversion['on_behalf_of'],
            'conversion_date_preference' => $conversion['conversion_date_preference']
        ]);
        $this->logOut();
        if ($response->successful()) {
            //dd($response->object());
            return $response->object();
        } else {
            throw new Exception(json_encode($response->object()->error_messages));
        }
    }

    public function createBeneficiaryDetail($beneficiary)
    {
        $this->logIn();
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'X-Auth-Token' => $this->x_auth_token
        ])->post(config('app.currency.url') . '/beneficiaries/create', [
            'name' => $beneficiary['name'],
            'bank_account_holder_name' => $beneficiary['bank_account_holder_name'],
            'bank_country' => $beneficiary['bank_country'],
            'currency' => $beneficiary['currency'],
            'account_number' => $beneficiary['account_number'],
            'routing_code_type_1' => $beneficiary['routing_code_type_1'],
            'routing_code_value_1' => $beneficiary['routing_code_value_1'],
            'iban' => $beneficiary['iban'],
            'on_behalf_of' => $beneficiary['on_behalf_of']
        ]);
        $this->logOut();
        if ($response->successful()) {
            //dd($response->object());
            return $response->object();
        } else {
            throw new Exception(json_encode($response->object()->error_messages));
        }
    }

    public function BalanceCurrencyCloud($balance)
    {
        $this->logIn();
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'X-Auth-Token' => $this->x_auth_token
        ])->get(config('app.currency.url') . '/balances/' . $balance['currency'], [
            'currency' => $balance['currency'],
            'on_behalf_of' => $balance['on_behalf_of']
        ]);
        $this->logOut();
        if ($response->successful()) {
            return $response->object();
        } else {
            throw new Exception(json_encode($response->object()->error_messages));
        }
    }

    public function createPaymentDetail($payment)
    {
        $this->logIn();
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'X-Auth-Token' => $this->x_auth_token
        ])->post(config('app.currency.url') . '/payments/create', [
            'beneficiary_id' => $payment['beneficiary_id'],
            'reference' => $payment['reference'],
            'unique_request_id' => str::uuid(),
            'reason' => $payment['reason'],
            'currency' => $payment['currency'],
            'amount' => $payment['amount'],
            'payment_type' => 'regular',
            'on_behalf_of' => $payment['on_behalf_of']
        ]);
        $this->logOut();
        if ($response->successful()) {
            return $response->object();
        } else {
            throw new Exception(json_encode($response->object()->error_messages));
        }
    }
}

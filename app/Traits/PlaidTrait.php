<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;
use Exception;

trait PlaidTrait
{
    public function createLinkToken($user_account)
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json'
        ])->post(config('app.plaid.url') . '/link/token/create', [
            'client_id' => config('app.plaid.client_id'),
            'secret' => config('app.plaid.secret'),
            'client_name' => $user_account->legal_first_name . ' ' . $user_account->legal_last_name,
            'user' => ["client_user_id" => $user_account->customer_uuid],
            'products' =>  ["auth"],
            'country_codes' => ["US"],
            'language' => 'en',
            'webhook' => "https://c292-103-249-233-56.ngrok.io/api/v1/get-account-id", //url('/api/v1/get-account-id')
            'redirect_uri' => "https://c292-103-249-233-56.ngrok.io" //url('/')
        ]);
        if ($response->successful()) {
            return $response->object();
        } else {
            throw new Exception($response->object()->error_message);
        }
    }

    public function createAccessToken()
    {
        //TODO:api call for create access token
    }

    public function createProcessorToken()
    {
        //TODO:api call for processor token
    }
}

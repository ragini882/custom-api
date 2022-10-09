<?php

namespace App\Traits;

use DwollaSwagger;

trait DwollaTrait
{
    private $apiClient;

    public function __construct()
    {
        DwollaSwagger\Configuration::$username = config('app.dwolla.key');
        DwollaSwagger\Configuration::$password = config('app.dwolla.secret');

        # For Sandbox
        $this->apiClient = new DwollaSwagger\ApiClient(config('app.dwolla.url'));

        # set access token
        $tokensApi = new DwollaSwagger\TokensApi($this->apiClient);

        DwollaSwagger\Configuration::$access_token = $tokensApi->token()->access_token;
    }

    public function createCustomer($user_data)
    {
        $customersApi = new DwollaSwagger\CustomersApi($this->apiClient);
        $customer = $customersApi->create($user_data);
        $response = explode('/', parse_url((string)$customer, PHP_URL_PATH));
        return (object)[
            "type" => $response[1],
            "uuid" => $response[2]
        ];
    }

    public function addBank($bank_data, $customer_uuid)
    {
        $fundingApi = new DwollaSwagger\FundingsourcesApi($this->apiClient);
        $fundingSource = $fundingApi->createCustomerFundingSource($bank_data, config('app.dwolla.url') . "/customers/" . $customer_uuid);
        $response = explode('/', parse_url((string)$fundingSource, PHP_URL_PATH));
        return (object)[
            "type" => $response[1],
            "uuid" => $response[2]
        ];
    }
}

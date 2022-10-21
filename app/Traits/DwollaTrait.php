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

    public function addBank($bank_data, $customer_uuid, $add_deposit)
    {
        $fundingApi = new DwollaSwagger\FundingsourcesApi($this->apiClient);
        $fundingSource = $fundingApi->createCustomerFundingSource($bank_data, config('app.dwolla.url') . "/customers/" . $customer_uuid);
        if ($add_deposit) {
            $this->addMicroDeposits($fundingSource);
        }
        $response = explode('/', parse_url((string)$fundingSource, PHP_URL_PATH));
        $funding_source = (object)[
            "type" => $response[1],
            "uuid" => $response[2]
        ];
        return $funding_source;
    }

    private function addMicroDeposits($fundingSource)
    {
        $fundingApi = new DwollaSwagger\FundingsourcesApi($this->apiClient);
        $fundingApi->microDeposits(null, $fundingSource);
        $fundingApi->microDeposits([
            'amount1' => [
                'value' => '0.01',
                'currency' => 'USD'
            ],
            'amount2' => [
                'value' => '0.01',
                'currency' => 'USD'
            ]
        ], $fundingSource);
    }

    private function getFundingSources($user_account)
    {
        $customerUrl = config('app.dwolla.url') . "/customers/" . $user_account->customer_uuid;

        $fsApi = new DwollaSwagger\FundingsourcesApi($this->apiClient);

        $fundingSources = $fsApi->getCustomerFundingSources($customerUrl);
        $funding_data = $fundingSources->_embedded->{'funding-sources'};
        $bank_list = [];
        foreach ($funding_data as $key => $bank) {
            $bank_list[$key]['uuid'] = $bank->id;
            $bank_list[$key]['status'] = $bank->status;
            $bank_list[$key]['type'] = $bank->type;
            $bank_list[$key]['bankAccountType'] = $bank->bankAccountType ?? '';
            $bank_list[$key]['name'] = $bank->name;
            $bank_list[$key]['created'] = $bank->created;
            $bank_list[$key]['removed'] = $bank->removed;
            $bank_list[$key]['channels'] = $bank->channels;
        }
        return $bank_list;
    }

    private function addBalance($user_account, $balance_data)
    {
        $transfer_request = [
            '_links' => [
                'source' => [
                    'href' => config('app.dwolla.url') . "/funding-sources/" . $balance_data['bank_uuid']
                ],
                'destination' => [
                    'href' => config('app.dwolla.url') . "/funding-sources/" . $user_account['balance_account_uuid']
                ],
            ],
            'amount' => [
                'currency' => 'USD',
                'value' => $balance_data['balance_amount']
            ]
        ];

        $transferApi = new DwollaSwagger\TransfersApi($this->apiClient);
        $transferApi->create($transfer_request);
    }

    public function getTransaction($user_account)
    {
        $customerUrl = config('app.dwolla.url') . "/customers/" . $user_account->customer_uuid;
        $TransfersApi = new DwollaSwagger\TransfersApi($this->apiClient);
        $transfers = $TransfersApi->getCustomerTransfers($customerUrl);
        $transfers_data = $transfers->_embedded->{'transfers'};
        $bank_list = [];
        foreach ($transfers_data as $key => $bank) {
            $bank_list[$key]['uuid'] = $bank->id;
            $bank_list[$key]['status'] = $bank->status;
            $bank_list[$key]['amount'] = $bank->amount;
            $bank_list[$key]['created'] = $bank->created;
            $bank_list[$key]['clearing'] = $bank->clearing ?? '';
            $bank_list[$key]['individualAchId'] = $bank->individualAchId ?? '';
            $bank_list[$key]['metadata'] = $bank->metadata ?? '';
        }
        return $bank_list;
        $transfers->_embedded->{'transfers'}[0]->status; # => "pending"
    }

    private function c2cBalance($user_account, $balance_data)
    {
        $tax_amount = (1.5 / 100) * $balance_data['balance_amount'];
        $transfer_request = [
            '_links' => [
                'source' => [
                    'href' => config('app.dwolla.url') . "/funding-sources/" . $user_account['balance_account_uuid']
                ],
                'destination' => [
                    'href' => config('app.dwolla.url') . "/funding-sources/" . $balance_data['bank_uuid']
                ],
            ],
            'amount' => [
                'currency' => 'USD',
                'value' => $balance_data['balance_amount']
            ],
            'metadata' => [
                'note' => 'payment for completed work Dec. 1',
            ]
        ];

        if ($balance_data['transfer_type'] == 'instant') {
            $transfer_request['processingChannel'] = [
                'destination' => 'real-time-payments'
            ];
            $transfer_request['rtpDetails'] = [
                'destination' => [
                    'remittanceData' => 'ABC_123 Remittance Data'
                ]
            ];
            $transfer_request['fees'] = [
                [
                    '_links' => [
                        'charge-to' => [
                            'href' => config('app.dwolla.url') . "/customers/" . $user_account['customer_uuid']
                        ]
                    ],
                    'amount' => [
                        'value' => $tax_amount,
                        'currency' => 'USD'
                    ]
                ]
            ];
        } else {
            $transfer_request['clearing'] = [
                'source' => 'standard', //next-day,same-day,next-available,standard
                'destination' => 'next-available' //next-day,same-day,next-available
            ];
        }


        $transferApi = new DwollaSwagger\TransfersApi($this->apiClient);
        $transferApi->create($transfer_request);
    }
}

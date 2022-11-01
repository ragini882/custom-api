<?php

namespace App\Traits;

use DwollaSwagger;

trait DwollaTrait
{
    private $apiClient;

    private function init()
    {
        DwollaSwagger\Configuration::$username = config('app.dwolla.key');
        DwollaSwagger\Configuration::$password = config('app.dwolla.secret');

        # For Sandbox
        $this->apiClient = new DwollaSwagger\ApiClient(config('app.dwolla.url'));

        # set access token
        $tokensApi = new DwollaSwagger\TokensApi($this->apiClient);

        DwollaSwagger\Configuration::$access_token = $tokensApi->token()->access_token;
    }

    public function addWebhookUrl()
    {
        $this->init();
        $webhookApi = new DwollaSwagger\WebhooksubscriptionsApi($this->apiClient);
        $subscription = $webhookApi->create(array(
            'url' => 'https://f301-103-249-233-15.ngrok.io/api/v1/webhook/dwolla-status',
            'secret' => '1234567890',
        ));
    }

    public function deleteWebhookUrl()
    {
        $this->init();
        $webhookApi = new DwollaSwagger\WebhooksubscriptionsApi($this->apiClient);
        $webhookApi->deleteById('https://api-sandbox.dwolla.com/webhook-subscriptions/8d25ac6e-e6e9-40ce-a67b-e0115c79bc2c');
    }

    public function createCustomer($user_data)
    {
        $this->init();
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
        $this->init();
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
        $this->init();
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
        $this->init();
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
        $this->init();
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

    public function getBalance($user_account)
    {
        $this->init();
        $balance_data = [];
        $balance_account = $this->getFundingSources($user_account);
        foreach ($balance_account as $balance) {
            if ($balance['type'] == "balance") {
                $balance_data['uuid'] = $balance['uuid'];
            }
        }
        $fundingSourceUrl = config('app.dwolla.url') . "/funding-sources/" .  $balance_data['uuid'];
        $fsApi = new DwollaSwagger\FundingsourcesApi($this->apiClient);
        $fundingSource = $fsApi->getBalance($fundingSourceUrl);
        $balance_data['amount'] =  $fundingSource->balance->value;
        return $balance_data;
    }

    public function getTransaction($user_account)
    {
        $this->init();
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

    private function withdrawWalletBalance($user_account, $balance_data)
    {
        $this->init();
        $tax_amount = (1.5 / 100) * $balance_data['balance_amount'];
        $tax_amount = ($tax_amount > 15) ? 15 : $tax_amount;
        $transfer_request = [
            '_links' => [
                'source' => [
                    'href' => config('app.dwolla.url') . "/funding-sources/" . $user_account['balance_account_uuid']
                ],
                'destination' => [
                    'href' => config('app.dwolla.url') . "/funding-sources/" . $balance_data['bank_uuid']
                ],
            ]
        ];

        if ($balance_data['transfer_type'] == 'instant') {
            $transfer_request['amount'] = [
                'currency' => 'USD',
                'value' => $balance_data['balance_amount'] - $tax_amount
            ];
            $transfer_request['clearing'] = [
                'source' => 'next-available', //next-day,same-day,next-available,standard
                'destination' => 'next-available' //next-day,same-day,next-available
            ];
            $transferApi = new DwollaSwagger\TransfersApi($this->apiClient);
            $transferApi->create($transfer_request);

            $fees_request = [
                '_links' => [
                    'source' => [
                        'href' => config('app.dwolla.url') . "/funding-sources/" . $user_account['balance_account_uuid']
                    ],
                    'destination' => [
                        'href' => config('app.dwolla.url') . "/funding-sources/" . config('app.dwolla.balance_uuid')
                    ],
                ],
                'amount' => [
                    'currency' => 'USD',
                    'value' => $tax_amount,
                ],
                'clearing' => [
                    'source' => 'next-available', //next-day,same-day,next-available,standard
                    'destination' => 'next-available' //next-day,same-day,next-available
                ]
            ];
            $feeApi = new DwollaSwagger\TransfersApi($this->apiClient);
            $feeApi->create($fees_request);
        } else {
            $transfer_request['amount'] = [
                'currency' => 'USD',
                'value' => $balance_data['balance_amount'] - $tax_amount
            ];
            $transfer_request['clearing'] = [
                'source' => 'standard', //next-day,same-day,next-available,standard
                'destination' => 'next-available' //next-day,same-day,next-available
            ];

            $transferApi = new DwollaSwagger\TransfersApi($this->apiClient);
            $transferApi->create($transfer_request);
        }
    }


    private function groupContributeAmount($contribute_data)
    {
        $this->init();
        $transfer_request = [
            '_links' => [
                'source' => [
                    'href' => config('app.dwolla.url') . "/funding-sources/" . $contribute_data['source']
                ],
                'destination' => [
                    'href' => config('app.dwolla.url') . "/funding-sources/" . $contribute_data['destination']
                ],
            ],
            'amount' => [
                'currency' => 'USD',
                'value' => $contribute_data['amount']
            ]
        ];

        $transferApi = new DwollaSwagger\TransfersApi($this->apiClient);
        $transferApi->create($transfer_request);
    }
}

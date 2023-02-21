<?php

namespace App\Submodules\Library\Firebase\Provider;

use App\Submodules\Jobs\RiderLogJob;
use App\Submodules\Library\Firebase\FirebaseException;
use Illuminate\Support\Facades\Http;
use Throwable;

class DeepLinkServiceProvider
{

    protected $base_url = "https://firebasedynamiclinks.googleapis.com/v1/shortLinks?key=";

    /*
     * @param $referral_code
     * @return array
     * @throws Throwable
     */
    public function generateReferralDeepLink($referral_code, $organization_name)
    {
        try {
            $api_key = config('app.deep_link_web_api_key');
            $android_package = config('app.android_package_name');
            $ios_package = config('app.ios_package_name');
            $app_id = config('app.ios_app_id');
            $domain = config('app.deep_link_domain');
            $web_link = config('app.deep_link_web_link');
            $url = $this->base_url . $api_key;
            $data['longDynamicLink'] = $domain . "?link=" . $web_link . "?referral_code=" . $referral_code . "&apn=" . $android_package . "&ibi=" . $ios_package . "&isi=" . $app_id . "&st=" . $organization_name . " Referral Code";
            $header = [
                'Content-type' => 'application/json'
            ];
            $response_time = microtime(true);
            $response = Http::withHeaders($header)->post($url, $data);
            $url = $this->base_url . hide_string($api_key);
            $log_data = [
                'request_method' => 'POST',
                'request_url' => $url,
                'headers' => $header,
                'request' => $data,
                'response' => $response->object(),
                'response_time' => round((microtime(true) - $response_time), 4)
            ];
            $labels['api_type'] = 'deep-link-api';
            $job = new RiderLogJob([
                'client_name' => config('app.client_name'),
                'log_data' => $log_data,
                'labels' => $labels
            ]);
            dispatch_job($job, 'logs', 'log_worker');
            if ($response->successful()) {
                return $response->json('shortLink');
            }
            $message = $response->json('error')['message'];
            $code = $response->json('error')['code'];
            throw new FirebaseException($message, $code);
        } catch (Throwable $t) {
            throw $t;
        }
    }
}

<?php

namespace App\Traits;

use Twilio\Rest\Client;

trait TwilioTrait
{
    public function sendSms($phone, $message)
    {
        $client = new Client(config('app.twilio.sid'), config('app.twilio.token'));
        $client->messages->create($phone, [
            'from' => config('app.twilio.from'),
            'body' => $message
        ]);

        return true;
    }
}

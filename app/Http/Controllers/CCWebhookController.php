<?php

namespace App\Http\Controllers;

use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CCWebhookController extends Controller
{
    use ResponseTrait;

    public function webhookRequest(Request $request)
    {
        Log::channel('daily')->info(json_encode($request->all()));
    }
}

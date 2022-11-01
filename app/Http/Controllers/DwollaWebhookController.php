<?php

namespace App\Http\Controllers;

use App\Traits\ResponseTrait;
use App\Traits\DwollaTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DwollaWebhookController extends Controller
{
    use ResponseTrait, DwollaTrait;

    public function webhookRequest(Request $request)
    {
        Log::channel('daily')->info(json_encode($request->all()));
    }
}

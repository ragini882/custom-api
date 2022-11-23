<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\ScanQrTransferAmountRequest;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use App\Traits\DwollaTrait;

class ScanController extends Controller
{
    use ResponseTrait, DwollaTrait;
    public function scanQrTransferAmount(ScanQrTransferAmountRequest $request)
    {
        $auth_user = auth()->user();
        $this->scanQrTransferAmountToWallet($request->all(), $auth_user->userAccount);
        return $this->sendSuccessResponse('Balance transfer to user successfully.');
    }
}

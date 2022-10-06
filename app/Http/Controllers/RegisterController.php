<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegistrationRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Http\Requests\SendOtpRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\forgotPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\DwollaAccountRequest;
use App\Models\User;
use App\Models\UserDwollaAccount;
use App\Traits\ResponseTrait;
use App\Traits\TwilioTrait;
use App\Traits\DwollaTrait;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    use ResponseTrait, TwilioTrait, DwollaTrait;
    private $token_name = 'USER_TOKEN';

    public function registration(RegistrationRequest $request)
    {
        $data = [
            'first_name' => $request->input('first_name'),
            'last_name' => $request->input('last_name'),
            'email' => $request->input('email'),
            'password' => bcrypt($request->input('password')),
            'account_type' => $request->input('account_type')
        ];

        $user = User::create($data);
        $user['token'] =  $user->createToken($this->token_name)->plainTextToken;

        return $this->sendSuccessResponse('User register successfully.', $user);
    }

    public function sendOtp(SendOtpRequest $request)
    {
        $otp = rand(100000, 999999);
        $auth_user = auth()->user();
        $user = User::where('id', $auth_user->id)->first();
        $user->phone = $request->input('phone');
        $user->code = $request->input('phone_country_code');
        $user->otp = $otp;
        $user->save();
        $body_text = str_replace('{OTP}', $otp, trans('translation.response.otp.body'));
        $this->sendSms($user->code . $user->phone, $body_text);
        return $this->sendSuccessResponse('otp send successfully.', $user);
    }

    public function verifyOtp(VerifyOtpRequest $request)
    {
        $auth_user = auth()->user();
        $user = User::where('id', $auth_user->id)->where('otp', $request->input('otp'))->first();
        if (is_null($user)) {
            return $this->sendBadRequestResponse('Invalid OTP. Please try again.');
        }

        $user->otp = null;
        $user->is_phone_verified = 1;
        $user->save();
        return $this->sendSuccessResponse('otp verified successfully.', $user);
    }

    public function login(LoginRequest $request)
    {
        $data = [
            'code' => $request->input('phone_country_code'),
            'phone' => $request->input('phone'),
            'password' => $request->input('password'),
            'is_phone_verified' => 1
        ];

        if (Auth::attempt($data)) {
            $user = Auth::user();
            $user['token'] =  $user->createToken($this->token_name)->plainTextToken;
            return $this->sendSuccessResponse('User login successfully.', $user);
        } else {
            return $this->sendBadRequestResponse('Invalid phone number or password.');
        }
    }

    public function forgotPassword(forgotPasswordRequest $request)
    {
        $otp = rand(100000, 999999);
        $user = User::where('phone', $request->input('phone'))->where('code', $request->input('phone_country_code'))->first();
        if (is_null($user)) {
            return $this->sendBadRequestResponse('Invalid phone number. Please try again.');
        }
        $user->otp = $otp;
        $user->save();
        $body_text = str_replace('{OTP}', $otp, trans('translation.response.otp.body'));
        $this->sendSms($user->code . $user->phone, $body_text);
        $user['token'] =  $user->createToken($this->token_name)->plainTextToken;
        return $this->sendSuccessResponse('otp send successfully.', $user);
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $auth_user = auth()->user();
        $user = User::where('id', $auth_user->id)->first();
        $user->password = bcrypt($request->input('password'));
        $user->save();
        return $this->sendSuccessResponse('Password has been changed successfully.', $user);
    }

    public function createDwollaAccount(DwollaAccountRequest $request)
    {
        $auth_user = auth()->user();
        if (!$auth_user->is_account_verified) {
            $user = [
                "firstName" => $request->input('legal_first_name'),
                "lastName" => $request->input('legal_last_name'),
                "email" => $auth_user->email,
                "type" => strtolower($auth_user->account_type),
                "address1" => $request->input('street_address'),
                "address2" => $request->input('address_type'),
                "city" => $request->input('city'),
                "state" => $request->input('state'),
                "postalCode" => $request->input('zip_code'),
                "dateOfBirth" => date("Y-m-d", strtotime($request->input('dob'))),
                "ssn" => $request->input('ssn')
            ];

            $customer = $this->createCustomer($user);
            $user_dwolla_account = new UserDwollaAccount;
            $user_dwolla_account->user_id = $auth_user->id;
            $user_dwolla_account->customer_uuid = $customer->uuid;
            $user_dwolla_account->legal_first_name = $user['firstName'];
            $user_dwolla_account->legal_last_name = $user['lastName'];
            $user_dwolla_account->dob = $user['dateOfBirth'];
            $user_dwolla_account->ssn = $user['ssn'];
            $user_dwolla_account->street_address = $user['address1'];
            $user_dwolla_account->address_type = $user['address2'];
            $user_dwolla_account->city = $user['city'];
            $user_dwolla_account->state = $user['state'];
            $user_dwolla_account->zip_code = $user['postalCode'];
            $user_dwolla_account->save();

            $user = User::where('id', $auth_user->id)->first();
            $user->is_account_verified = 1;
            $user->save();
            $user['user_account'] = $user_dwolla_account;
            return $this->sendSuccessResponse('Account verified successfully.', $user);
        } else {
            return $this->sendBadRequestResponse('Account is already verified');
        }
    }
}

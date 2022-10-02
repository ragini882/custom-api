<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\validator as Validator;
use Brick\PhoneNumber\PhoneNumber;
use App\Traits\ResponseTrait;

class forgotPasswordRequest extends FormRequest
{
    use ResponseTrait;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'phone' => 'required',
            'phone_country_code' => 'required',
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            if (!$this->isPhoneNumberValid()) {
                $validator->errors()->add('phone', trans('translation.response.phone_invalid'));
            }
        });
    }

    private function isPhoneNumberValid()
    {
        if ($this->input('phone')) {
            try {
                $number = PhoneNumber::parse($this->input('phone_country_code') . $this->input('phone'));
                return $number->isValidNumber();
            } catch (\Throwable $th) {
                return false;
            }
        }
        return true;
    }

    protected function failedValidation(Validator $validator)
    {
        $this->sendValidationFailedResponse($validator->errors(), trans('translation.response.validation_failed'));
    }
}

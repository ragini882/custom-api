<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\validator as Validator;
use App\Traits\ResponseTrait;

class AddBankRequest extends FormRequest
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
            'bank_verify_type' => 'required|in:manual,instant',
            'routing_number' => 'required_unless:bank_verify_type,instant',
            'account_number' => 'required_unless:bank_verify_type,instant',
            'confirm_account_number' => 'required_unless:bank_verify_type,instant|same:account_number',
            'public_token' => 'required_unless:bank_verify_type,manual',
            'account_id' => 'required_unless:bank_verify_type,manual',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $this->sendValidationFailedResponse($validator->errors(), trans('translation.response.validation_failed'));
    }
}

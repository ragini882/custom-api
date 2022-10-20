<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\validator as Validator;
use App\Traits\ResponseTrait;

class C2cTransferRequest extends FormRequest
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
            'bank_uuid' => 'required',
            'balance_amount' => 'required',
            'transfer_type' => 'required'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $this->sendValidationFailedResponse($validator->errors(), trans('translation.response.validation_failed'));
    }
}

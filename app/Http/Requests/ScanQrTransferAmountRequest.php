<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\validator as Validator;
use App\Traits\ResponseTrait;

class ScanQrTransferAmountRequest extends FormRequest
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
            'customer_uuid' => 'required',
            'bank_uuid' => 'required',
            'amount' => 'required'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $this->sendValidationFailedResponse($validator->errors(), trans('translation.response.validation_failed'));
    }
}

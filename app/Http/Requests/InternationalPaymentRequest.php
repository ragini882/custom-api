<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\validator as Validator;
use App\Traits\ResponseTrait;

class InternationalPaymentRequest extends FormRequest
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
            'name' => 'required',
            'bank_account_holder_name' => 'required',
            'bank_country' => 'required',
            'currency' => 'required',
            'account_number' => 'required',
            'routing_code_type_1' => 'required',
            'routing_code_value_1' => 'required',
            'iban' => 'required',
            'on_behalf_of' => 'required',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $this->sendValidationFailedResponse($validator->errors(), trans('translation.response.validation_failed'));
    }
}

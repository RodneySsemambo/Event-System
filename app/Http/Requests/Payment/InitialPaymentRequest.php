<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class InitialPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone_number' => [
                'required',
                'string',
                'regex:/^\+256[0-9]{9}$/', // Ugandan local format: 0XXXXXXXXX
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'phone_number.regex' => 'Enter your number as +256XXXXXXXXX (include the country code).',
        ];
    }
}

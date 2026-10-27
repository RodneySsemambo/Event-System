<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use VincenzoRaco\TurnstileLaravel\Rules\TurnstileCheck;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            // 'cf-turnstile-response' => ['required', 'string', new TurnstileCheck],
        ];
    }

    public function messages(): array
    {
        return [
            'cf-turnstile-response.required' => 'Please complete the human verification.',
            'cf-turnstile-response.*'        => 'Human verification failed. Please try again.',
        ];
    }
}

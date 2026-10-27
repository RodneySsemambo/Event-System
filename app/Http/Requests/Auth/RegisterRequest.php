<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
//use VincenzoRaco\TurnstileLaravel\Rules\TurnstileCheck;


class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // anyone may attempt to register

    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(['client', 'provider'])],

            // Client-specific (optional at signup, can be completed later)
            'phone' => ['nullable', 'required_if:role,client', 'string', 'max:30'],
            'default_location' => ['nullable', 'string', 'max:255'],

            // Provider-specific (required at signup so a minimal profile exists)
            'business_name' => ['nullable', 'required_if:role,provider', 'string', 'max:255'],
            'category_id' => ['nullable', 'required_if:role,provider', 'exists:categories,id'],
            'location' => ['nullable', 'required_if:role,provider', 'string', 'max:255'],

            //'cf-turnstile-response' => ['required', 'string', new TurnstileCheck],
            'agreed_to_terms' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'cf-turnstile-response.required' => 'Please complete the human verification.',
            'cf-turnstile-response.*'        => 'Human verification failed. Please try again.',
            'agreed_to_terms.accepted' => 'You must agree to the Terms of Service to register.',
        ];
    }
}

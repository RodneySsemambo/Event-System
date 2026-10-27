<?php

namespace App\Http\Requests\Provider;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProviderProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Ownership is enforced by ProviderProfilePolicy in the controller,
        // not here — FormRequest authorize() only checks "is this request
        // shaped correctly", not "do they own this specific record".
        return true;
    }

    public function rules(): array
    {
        return [
            'business_name' => ['sometimes', 'string', 'max:255'],
            'category_id' => ['sometimes', 'exists:categories,id'],
            'location' => ['sometimes', 'string', 'max:255'],
            'whatsapp_number' => ['nullable', 'regex:/^[0-9]{9,15}$/'], // digits only, international format, no + or spaces
            'bio' => ['nullable', 'string', 'max:2000'],
            'price_range_min' => ['nullable', 'numeric', 'min:0'],
            'price_range_max' => ['nullable', 'numeric', 'gte:price_range_min'],
            'availability_status' => ['sometimes', 'in:available,busy,unavailable'],

            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'banner' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }
}

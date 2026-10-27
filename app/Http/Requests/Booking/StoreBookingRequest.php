<?php

namespace App\Http\Requests\Booking;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // role check happens at the route middleware level ('role:client')
    }

    public function rules(): array
    {
        return [
            'provider_profile_id' => ['required', 'exists:provider_profiles,id'],
            'event_type' => ['required', 'string', 'max:255'],
            'event_date' => ['required', 'date', 'after_or_equal:today'],
            'location' => ['required', 'string', 'max:255'],
            'guest_count' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}

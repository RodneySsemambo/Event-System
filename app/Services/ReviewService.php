<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Review;
use Illuminate\Validation\ValidationException;

class ReviewService
{
    public function create(Booking $booking, array $data): Review
    {
        if ($booking->status !== 'completed') {
            throw ValidationException::withMessages([
                'booking' => ['You can only review a completed booking.'],
            ]);
        }

        if ($booking->review()->exists()) {
            throw ValidationException::withMessages([
                'booking' => ['This booking already has a review.'],
            ]);
        }

        return $booking->review()->create([
            'rating'              => $data['rating'],
            'comment'             => $data['comment'] ?? null,
            'client_profile_id'   => $booking->client_profile_id,
            'provider_profile_id' => $booking->provider_profile_id,
        ]);
    }
}

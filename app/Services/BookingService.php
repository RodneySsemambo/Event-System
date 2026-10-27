<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class BookingService
{
    public function __construct(private NotificationService $notifications) {}

    public function createBooking(User $client, array $data): Booking
    {
        if (! $client->clientProfile) {
            throw ValidationException::withMessages([
                'client' => ['Register as a client to make a booking.'],
            ]);
        }

        $booking = Booking::create([
            ...$data,
            'client_profile_id' => $client->clientProfile->id,
            'status'            => 'pending',
        ]);

        $booking->load('providerProfile.user');

        $this->notifications->bookingCreated($booking->providerProfile->user, $booking);

        return $booking->load('providerProfile');
    }

    public function acceptBooking(Booking $booking, ?float $quotedAmount = null): Booking
    {
        $providerPrice = $quotedAmount
            ?? $booking->providerProfile->price_range_min
            ?? 0;

        $commissionRate = 0.10; // 10% Evently fee
        $commission     = round($providerPrice * $commissionRate, 2);
        $payout         = round($providerPrice - $commission, 2);

        $booking->update([
            'status'            => 'accepted',
            'quoted_amount'     => $providerPrice,
            'commission_amount' => $commission,
            'provider_payout'   => $payout,
        ]);

        $booking->load('clientProfile.user');
        $this->notifications->bookingAccepted($booking->clientProfile->user, $booking);

        return $booking->fresh();
    }

    public function declineBooking(Booking $booking): Booking
    {
        $booking->update(['status' => 'declined']);

        $booking->load('clientProfile.user');
        $this->notifications->bookingDeclined($booking->clientProfile->user, $booking);

        return $booking->fresh();
    }

    public function cancelBooking(Booking $booking): Booking
    {
        $booking->update(['status' => 'cancelled']);

        return $booking->fresh();
    }

    public function cancelOnPaymentFailure(Booking $booking): Booking
    {
        $booking->update(['status' => 'cancelled']);

        $booking->load('clientProfile.user');
        $this->notifications->paymentFailed($booking->clientProfile->user, $booking);

        return $booking->fresh();
    }

   public function completeBooking(Booking $booking): Booking
{
    if ($booking->payment?->status !== 'successful') {
        throw ValidationException::withMessages([
            'payment' => ['This booking cannot be completed until payment has been confirmed.'],
        ]);
    }

    // Ensure commission columns are populated even if they were missed
    // at accept time (e.g. bookings created before this feature existed).
    if ($booking->provider_payout === null) {
        $price      = $booking->quoted_amount
            ?? $booking->providerProfile->price_range_min
            ?? 0;
        $commission = round($price * 0.10, 2);

        $booking->quoted_amount     = $price;
        $booking->commission_amount = $commission;
        $booking->provider_payout   = round($price - $commission, 2);
    }

    $booking->status = 'completed';
    $booking->save();

    $booking->load('clientProfile.user');
    $this->notifications->bookingCompleted($booking->clientProfile->user, $booking);

    return $booking->fresh();
}
}

<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use MarzPay\Exceptions\MarzPayException;
use MarzPay\Laravel\Facades\MarzPay;

class PaymentService
{
    public function __construct(
        private BookingService $bookings,
        private NotificationService $notifications,
    ) {}

    public function initiateCollection(Booking $booking, string $phoneNumber): Payment
    {
        if ($booking->status !== 'accepted') {
            throw ValidationException::withMessages(['booking' => ['Payment can only be initiated for an accepted booking.']]);
        }

        $alreadyPaid = Payment::where('booking_id', $booking->id)->where('status', 'successful')->exists();
        if ($alreadyPaid) {
            throw ValidationException::withMessages(['booking' => ['This booking has already been paid for.']]);
        }

        $amount = $this->resolveAmount($booking);
        if ($amount <= 0) {
            throw ValidationException::withMessages(['amount' => ['This provider has not set a price yet.']]);
        }

        $reference = (string) Str::uuid();

        $payment = Payment::create([
            'booking_id'          => $booking->id,
            'client_profile_id'   => $booking->client_profile_id,
            'provider_profile_id' => $booking->provider_profile_id,
            'amount'              => $amount,
            'currency'            => 'UGX',
            'phone_number'        => $phoneNumber,
            'marzpay_reference'   => $reference,
            'status'              => 'pending',
            'type'                => 'collection',
        ]);

        try {
            $response = MarzPay::collections()->collectMoney([
                'phone_number' => $phoneNumber,
                'amount'       => $amount,
                'description'  => "Evently booking #{$booking->id}",
                'country'      => 'UG',
                'reference'    => $reference,
            ]);

            $marzReference = $response['data']['transaction']['reference'] ?? $reference;
            $marzUuid      = $response['data']['transaction']['uuid'] ?? null;

            $payment->update(['marzpay_reference' => $marzReference, 'marzpay_uuid' => $marzUuid]);

        } catch (MarzPayException $e) {
            Log::error('MarzPay collection failed', ['booking_id' => $booking->id, 'message' => $e->getMessage()]);
            $payment->update(['status' => 'failed']);

            $detail = strtolower($e->getMessage());
            $message = (str_contains($detail, 'balance') || str_contains($detail, 'insufficient')) 
                ? 'Your mobile money wallet does not have enough balance. Top up and try again.'
                : 'Could not initiate payment. Please contact our customer support.';

            throw ValidationException::withMessages(['payment' => [$message]]);
        }

        return $payment->fresh();
    }

    public function handleWebhook(array $payload): void
    {
        $inner = $payload['data'] ?? $payload;
        $reference = $inner['transaction']['reference'] ?? null;
        $status = $inner['transaction']['status'] ?? null;
        $description = $inner['transaction']['description'] ?? null;

        if (! $reference) return;

        $payment = Payment::where('marzpay_reference', $reference)->first();
        if (! $payment || in_array($payment->status, ['successful', 'failed'], true)) return;

        $isSuccess = in_array($status, ['successful', 'completed'], true) || ($inner['event_type'] ?? null) === 'collection.completed';

        $payment->update([
            'status' => $isSuccess ? 'successful' : 'failed',
            'failure_reason' => $isSuccess ? null : $description,
            'raw_payload' => $payload,
        ]);

        $booking = $payment->booking;
        if ($isSuccess) {
            $this->notifications->paymentSuccessful($booking->clientProfile->user, $booking);
            $this->notifications->paymentSuccessful($booking->providerProfile->user, $booking);
            $booking->update(['status' => 'completed']);
            $this->notifications->bookingCompleted($booking->clientProfile->user, $booking);
        } else {
            $this->notifications->paymentFailed($booking->clientProfile->user, $booking);
        }
    }

    public function resolveAmount(Booking $booking): float
    {
        return (float) ($booking->quoted_amount ?? $booking->providerProfile->price_range_min ?? 0);
    }
}
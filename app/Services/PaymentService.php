<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use function NileSquad\NylonPay\createNylonPay;

class PaymentService
{
    public function __construct(
        private BookingService $bookings,
        private NotificationService $notifications,
    ) {}

    public function initiateCollection(Booking $booking, string $phoneNumber): Payment
    {
        if ($booking->status !== 'accepted') {
            throw ValidationException::withMessages([
                'booking' => ['Payment can only be initiated for an accepted booking.'],
            ]);
        }

        $alreadyPaid = Payment::where('booking_id', $booking->id)
            ->where('status', 'successful')
            ->exists();

        if ($alreadyPaid) {
            throw ValidationException::withMessages([
                'booking' => ['This booking has already been paid for.'],
            ]);
        }

        $amount = $this->resolveAmount($booking);

        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => ['This provider has not set a price yet.'],
            ]);
        }

        // Nylon Pay requires a valid UUID v4 for the reference.
        $reference = (string) Str::uuid();

        $payment = Payment::create([
            'booking_id'          => $booking->id,
            'client_profile_id'   => $booking->client_profile_id,
            'provider_profile_id' => $booking->provider_profile_id,
            'amount'              => (int) $amount,
            'currency'            => 'UGX',
            'phone_number'        => $phoneNumber,
            'marzpay_reference'   => $reference,
            'status'              => 'pending',
            'type'                => 'collection',
        ]);

        try {
            $nylon = createNylonPay([
                'apiKey'    => config('services.nylonpay.api_key'),
                'apiSecret' => config('services.nylonpay.api_secret'),
                'baseUrl'   => config('services.nylonpay.base_url'),
            ]);

            // Use collectPayment, NOT collectPaymentAndResolve.
            // We rely on the webhook to finalize the payment.
            $paymentInstance = $nylon->collectPayment([
                'amount'      => (int) $amount,
                'currency'    => 'UGX',
                'method'      => 'mobileMoney',
                'customer'    => [
                    'name'        => $booking->clientProfile->user->name,
                    'phoneNumber' => $phoneNumber,
                ],
                'description' => "Evently booking #{$booking->id}",
                'reference'   => $reference,
                'prompt'      => 'pin', // Required for sandbox simulator
            ]);

            $payment->update([
                'raw_payload' => (array) $paymentInstance,
            ]);
        } catch (\Throwable $e) {
            Log::error('Nylon Pay collection failed', [
                'booking_id' => $booking->id,
                'phone'      => $phoneNumber,
                'message'    => $e->getMessage(),
            ]);

            $payment->update(['status' => 'failed']);

            throw ValidationException::withMessages([
                'payment' => ['Payment error: ' . $e->getMessage()],
            ]);
        }

        return $payment->fresh();
    }

    public function handleWebhook(array $payload): void
    {
        $reference = $payload['transaction']['reference'] ?? $payload['reference'] ?? null;
        $status    = $payload['transaction']['status'] ?? $payload['status'] ?? null;
        $message   = $payload['transaction']['failure_reason'] ?? $payload['error'] ?? null;

        if (! $reference) {
            Log::warning('Nylon Pay webhook with no reference', $payload);
            return;
        }

        $payment = Payment::where('marzpay_reference', $reference)->first();

        if (! $payment) {
            Log::warning('Nylon Pay webhook for unknown reference', ['reference' => $reference]);
            return;
        }

        if (in_array($payment->status, ['successful', 'failed'], true)) {
            return;
        }

        $isSuccess = in_array($status, ['success', 'successful', 'completed'], true);

        $payment->update([
            'status'         => $isSuccess ? 'successful' : 'failed',
            'failure_reason' => $isSuccess ? null : $message,
            'raw_payload'    => $payload,
        ]);

        if ($isSuccess) {
            $this->handleSuccessfulPayment($payment);
        } else {
            $this->notifications->paymentFailed($payment->booking->clientProfile->user, $payment->booking);
        }
    }

    private function handleSuccessfulPayment(Payment $payment): void
    {
        $booking = $payment->booking;

        $this->notifications->paymentSuccessful($booking->clientProfile->user, $booking);
        $this->notifications->paymentSuccessful($booking->providerProfile->user, $booking);
        $booking->update(['status' => 'completed']);
        $this->notifications->bookingCompleted($booking->clientProfile->user, $booking);
    }

    public function resolveAmount(Booking $booking): float
    {
        return (float) ($booking->quoted_amount
            ?? $booking->providerProfile->price_range_min
            ?? 0);
    }

    public function refreshFromProvider(Payment $payment): Payment
    {
        // The SDK's getStatus() has a 308 redirect bug.
        // We rely entirely on the webhook to update this payment.
        return $payment;
    }

    public function simulateDemoPayment(Booking $booking): Payment
    {
        $reference = 'DEMO-' . strtoupper(Str::random(10));

        $payment = Payment::create([
            'booking_id'          => $booking->id,
            'client_profile_id'   => $booking->client_profile_id,
            'provider_profile_id' => $booking->provider_profile_id,
            'amount'              => (int) $booking->quoted_amount,
            'currency'            => 'UGX',
            'phone_number'        => '0700000000',
            'marzpay_reference'   => $reference,
            'status'              => 'successful',
            'type'                => 'collection',
        ]);

        // Manually update the booking status and trigger notifications
        $booking->update(['status' => 'completed']);

        $this->notifications->paymentSuccessful($booking->clientProfile->user, $booking);
        $this->notifications->paymentSuccessful($booking->providerProfile->user, $booking);
        $this->notifications->bookingCompleted($booking->clientProfile->user, $booking);

        return $payment;
    }
}

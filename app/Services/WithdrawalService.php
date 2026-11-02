<?php

namespace App\Services;

use App\Models\ProviderProfile;
use App\Models\Withdrawal;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use MarzPay\Exceptions\MarzPayException;
use MarzPay\Laravel\Facades\MarzPay;

class WithdrawalService
{
    public const MINIMUM = 1000;

    public function balance(ProviderProfile $provider): array
    {
        $earned = (float) $provider->bookings()->where('status', 'completed')->sum('provider_payout');
        $withdrawn = (float) $provider->withdrawals()->whereIn('status', ['pending', 'processing', 'successful'])->sum('amount');

        return [
            'earned' => $earned,
            'withdrawn' => $withdrawn,
            'available' => max(0, $earned - $withdrawn),
        ];
    }

    public function request(ProviderProfile $provider, float $amount, string $phoneNumber): Withdrawal
    {
        if ($amount < self::MINIMUM) {
            throw ValidationException::withMessages(['amount' => ['Minimum withdrawal is UGX ' . number_format(self::MINIMUM)]]);
        }

        $balance = $this->balance($provider);
        if ($amount > $balance['available']) {
            throw ValidationException::withMessages(['amount' => ['You can only withdraw up to UGX ' . number_format($balance['available'])]]);
        }

        $reference = (string) Str::uuid();

        $withdrawal = Withdrawal::create([
            'provider_profile_id' => $provider->id,
            'amount' => $amount,
            'phone_number' => $phoneNumber,
            'status' => 'pending',
            'marzpay_reference' => $reference,
        ]);

        try {
            $response = MarzPay::disbursements()->sendMoney([
                'phone_number' => $phoneNumber,
                'amount'       => $amount,
                'country'      => 'UG',
                'description'  => "Evently payout #{$withdrawal->id}",
                'reference'    => $reference,
            ]);

            $marzRef = $response['data']['transaction']['reference'] ?? $reference;

            $withdrawal->update([
                'marzpay_reference' => $marzRef,
                'status'            => 'processing',
            ]);

        } catch (MarzPayException $e) {
            Log::error('Withdrawal failed', ['id' => $withdrawal->id, 'message' => $e->getMessage()]);
            $withdrawal->update(['status' => 'failed', 'failure_reason' => $e->getMessage()]);

            $raw = strtolower($e->getMessage());
            $message = str_contains($raw, 'timed out') || str_contains($raw, 'connect')
                ? 'Our payment provider is currently unreachable. Try again later.'
                : 'Withdrawal could not be processed. Please try again.';

            throw ValidationException::withMessages(['amount' => [$message]]);
        }

        return $withdrawal->fresh();
    }

    public function handleWebhook(array $payload): void
    {
        $inner = $payload['data'] ?? $payload;
        $reference = $inner['transaction']['reference'] ?? null;
        $status = $inner['transaction']['status'] ?? null;

        if (! $reference) return;

        $withdrawal = Withdrawal::where('marzpay_reference', $reference)->first();
        if (! $withdrawal || in_array($withdrawal->status, ['successful', 'failed'], true)) return;

        $isSuccess = in_array($status, ['successful', 'completed'], true) || ($inner['event_type'] ?? null) === 'disbursement.completed';

        $withdrawal->update([
            'status' => $isSuccess ? 'successful' : 'failed',
            'failure_reason' => $isSuccess ? null : ($inner['transaction']['description'] ?? 'Payout failed.'),
            'raw_payload' => $payload,
        ]);
    }
}
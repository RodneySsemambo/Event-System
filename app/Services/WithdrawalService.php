<?php

namespace App\Services;

use App\Models\ProviderProfile;
use App\Models\Withdrawal;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use function NileSquad\NylonPay\createNylonPay;

class WithdrawalService
{
    public const MINIMUM = 100;

    public function balance(ProviderProfile $provider): array
    {
        $earned = (float) $provider->bookings()
            ->where('status', 'completed')
            ->sum('provider_payout');

        $withdrawn = (float) $provider->withdrawals()
            ->whereIn('status', ['pending', 'processing', 'successful'])
            ->sum('amount');

        return [
            'earned'    => $earned,
            'withdrawn' => $withdrawn,
            'available' => max(0, $earned - $withdrawn),
        ];
    }

    public function request(ProviderProfile $provider, float $amount, string $phoneNumber): Withdrawal
    {
        if ($amount < self::MINIMUM) {
            throw ValidationException::withMessages([
                'amount' => ['Minimum withdrawal is UGX ' . number_format(self::MINIMUM) . '.'],
            ]);
        }

        $balance = $this->balance($provider);

        if ($amount > $balance['available']) {
            throw ValidationException::withMessages([
                'amount' => ['You can only withdraw up to UGX ' . number_format($balance['available']) . '.'],
            ]);
        }

        // Nylon Pay requires a 13-15 character reference
        $reference = (string) Str::random();

        $withdrawal = Withdrawal::create([
            'provider_profile_id' => $provider->id,
            'amount'              => $amount,
            'phone_number'        => $phoneNumber,
            'status'              => 'pending',
            'marzpay_reference'   => $reference,
        ]);

        try {
            $nylon = createNylonPay([
                'apiKey'    => config('services.nylonpay.api_key'),
                'apiSecret' => config('services.nylonpay.api_secret'),
                'baseUrl'   => config('services.nylonpay.base_url'),
            ]);

            $payout = $nylon->makePayout([
                'amount'      => (int) $amount,
                'currency'    => 'UGX',
                'customer'    => [
                    'name'        => $provider->user->name,
                    'phoneNumber' => $phoneNumber,
                ],
                'destination' => [
                    'account_holder_name' => $provider->user->name,
                    'account_number'      => $phoneNumber,
                ],
                'description' => "Evently payout #{$withdrawal->id}",
                'reference'   => $reference,

            ]);

            $withdrawal->update([
                'marzpay_uuid' => $payout->getId(),
                'status'       => 'processing',
                'raw_payload'  => (array) $payout,
            ]);
        } catch (\Throwable $e) {
            Log::error('Withdrawal failed', [
                'withdrawal_id' => $withdrawal->id,
                'amount'        => $amount,
                'phone'         => $phoneNumber,
                'message'       => $e->getMessage(),
            ]);

            $withdrawal->update([
                'status'         => 'failed',
                'failure_reason' => $e->getMessage(),
            ]);

            $raw = strtolower($e->getMessage());
            $message = 'Withdrawal could not be processed. Please try again later.';

            if (str_contains($raw, 'timed out') || str_contains($raw, 'could not connect')) {
                $message = 'Our payment provider is currently unreachable. Please try again in a few minutes.';
            } elseif (str_contains($raw, 'insufficient balance')) {
                $message = 'The payout wallet does not have enough balance to complete this withdrawal.';
            }

            throw ValidationException::withMessages([
                'amount' => [$message],
            ]);
        }

        return $withdrawal->fresh();
    }

    public function handleWebhook(array $payload): void
    {
        $reference = $payload['reference'] ?? $payload['data']['reference'] ?? null;
        $status    = $payload['status'] ?? $payload['data']['status'] ?? null;

        if (! $reference) {
            return;
        }

        $withdrawal = Withdrawal::where('marzpay_reference', $reference)->first();

        if (! $withdrawal) {
            return;
        }

        if (in_array($withdrawal->status, ['successful', 'failed'], true)) {
            return;
        }

        $isSuccess = in_array($status, ['successful', 'success', 'completed'], true);

        $withdrawal->update([
            'status'         => $isSuccess ? 'successful' : 'failed',
            'failure_reason' => $isSuccess ? null : ($payload['message'] ?? $payload['data']['message'] ?? null),
            'raw_payload'    => $payload,
        ]);
    }
}

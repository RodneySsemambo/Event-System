<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    public function send(string $phoneNumber, string $message): void
    {
        $apiKey = config('services.yoola.api_key');

        if (! $apiKey) {
            Log::warning('Yoola SMS: API key missing');
            return;
        }

        // Yoola expects local format: 07XXXXXXXX
        // Convert +256XXXXXXXXX -> 0XXXXXXXXX
        $normalized = $phoneNumber;
        if (str_starts_with($phoneNumber, '+256')) {
            $normalized = '0' . substr($phoneNumber, 4);
        } elseif (str_starts_with($phoneNumber, '256')) {
            $normalized = '0' . substr($phoneNumber, 3);
        }

        try {
            $response = Http::timeout(30)->post('https://yoolasms.com/api/v1/send.php', [
                'phone'   => $normalized,
                'message' => $message,
                'api_key' => $apiKey,
                'sender'  => config('services.yoola.sender', 'YoolaSMS'),
            ]);

            $body = $response->json();

            Log::info('Yoola SMS attempt', [
                'to'       => $normalized,
                'status'   => $body['status'] ?? null,
                'code'     => $body['code'] ?? null,
                'balance'  => $body['balance'] ?? null,
                'response' => $body,
            ]);

            if (($body['status'] ?? null) !== 'success') {
                Log::error('Yoola SMS failed', ['phone' => $normalized, 'response' => $body]);
            }
        } catch (\Throwable $e) {
            Log::error('Yoola SMS exception', [
                'phone' => $normalized,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CheckMarzPayHealth extends Command
{
    protected $signature = 'marzpay:health-check';
    protected $description = 'Ping the MarzPay API and alert on state transitions.';

    private const CACHE_KEY = 'marzpay:health:last-state';
    private const ALERT_EMAIL = 'ssemamborodney94@gmail.com';
    private const BASE_URL = 'https://wallet.wearemarz.com';
    private const TIMEOUT = 10;

    public function handle(): int
    {
        $wasUp = Cache::get(self::CACHE_KEY) === 'up';
        $isUp  = $this->probe();

        // Nothing changed — stay silent
        if ($wasUp === $isUp) {
            $this->line($isUp ? 'MarzPay: still UP' : 'MarzPay: still DOWN');
            return self::SUCCESS;
        }

        Cache::put(self::CACHE_KEY, $isUp ? 'up' : 'down', now()->addDay());

        if (! $isUp) {
            $this->alertDown();
        } else {
            $this->alertRecovered();
        }

        return self::SUCCESS;
    }

    private function probe(): bool
    {
        try {
            // Any HTTP response means the server is reachable. We don't
            // care if it returns 401 or 404 — that's still "up".
            Http::timeout(self::TIMEOUT)
                ->withOptions(['allow_redirects' => ['max' => 1]])
                ->get(self::BASE_URL);
            return true;
        } catch (\Throwable $e) {
            Log::warning('MarzPay health probe failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    private function alertDown(): void
    {
        $message = 'MarzPay API is UNREACHABLE. Payments and withdrawals will fail until it recovers.';
        Log::error($message);

        $this->notify(self::ALERT_EMAIL, 'MarzPay is DOWN', $message);
        $this->error($message);
    }

    private function alertRecovered(): void
    {
        $message = 'MarzPay API is back online. Payments and withdrawals should work again.';
        Log::info($message);

        $this->notify(self::ALERT_EMAIL, 'MarzPay is BACK UP', $message);
        $this->info($message);
    }

    private function notify(string $to, string $subject, string $body): void
    {
        try {
            Mail::raw($body, function ($m) use ($to, $subject) {
                $m->to($to)->subject('[Evently] ' . $subject);
            });
        } catch (\Throwable $e) {
            Log::error('Failed to send MarzPay health alert email', ['error' => $e->getMessage()]);
        }
    }
}

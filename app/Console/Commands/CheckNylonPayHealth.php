<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CheckNylonPayHealth extends Command
{
    protected $signature   = 'nylonpay:health-check';
    protected $description = 'Ping the Nylon Pay API and alert on state transitions.';

    private const CACHE_KEY = 'nylonpay:health:last-state';
    private const ALERT_EMAIL = 'ssemamborodney94@gmail.com';
    private const BASE_URL = 'https://api.nylonpay.nilesquad.com/api/services';
    private const TIMEOUT = 10;

    public function handle(): int
    {
        $wasUp = Cache::get(self::CACHE_KEY) === 'up';
        $isUp  = $this->probe();

        if ($wasUp === $isUp) {
            $this->line($isUp ? 'Nylon Pay: still UP' : 'Nylon Pay: still DOWN');
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
            Http::timeout(self::TIMEOUT)->get(self::BASE_URL);
            return true;
        } catch (\Throwable $e) {
            Log::warning('Nylon Pay health probe failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    private function alertDown(): void
    {
        $message = 'Nylon Pay API is UNREACHABLE. Payments and withdrawals will fail until it recovers.';
        Log::error($message);
        $this->notify(self::ALERT_EMAIL, 'Nylon Pay is DOWN', $message);
        $this->error($message);
    }

    private function alertRecovered(): void
    {
        $message = 'Nylon Pay API is back online. Payments and withdrawals should work again.';
        Log::info($message);
        $this->notify(self::ALERT_EMAIL, 'Nylon Pay is BACK UP', $message);
        $this->info($message);
    }

    private function notify(string $to, string $subject, string $body): void
    {
        try {
            Mail::raw($body, function ($m) use ($to, $subject) {
                $m->to($to)->subject('[Evently] ' . $subject);
            });
        } catch (\Throwable $e) {
            Log::error('Failed to send Nylon Pay health alert email', ['error' => $e->getMessage()]);
        }
    }
}

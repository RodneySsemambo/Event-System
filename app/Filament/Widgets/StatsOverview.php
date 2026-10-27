<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\ProviderProfile;
use App\Models\User;
use App\Models\Withdrawal;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class StatsOverview extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 1;
    protected int|string|array $columnSpan = 'full';
    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        $start = $this->filters['startDate'] ?? now()->startOfMonth();
        $end   = $this->filters['endDate'] ?? now();

        // 1. ALL-TIME WALLET MATH
        $totalCollected = Booking::where('status', 'completed')->sum('quoted_amount');
        $totalCommission = Booking::where('status', 'completed')->sum('commission_amount');
        $totalWithdrawn = Withdrawal::where('status', 'successful')->sum('amount');

        $walletBalance = $totalCollected - $totalWithdrawn;
        $providerOwed = $walletBalance - $totalCommission;

        // 2. FILTERED STATS (Date Picker)
        $filteredSuccessful = Booking::where('status', 'completed')
            ->whereBetween('created_at', [$start, $end])
            ->sum('quoted_amount');

        $filteredFailedCount = Payment::where('status', 'failed')
            ->whereBetween('created_at', [$start, $end])
            ->count();

        // 3. SYSTEM HEALTH & COUNTS
        $verifiedProviders = ProviderProfile::where('verification_status', 'verified')->count();
        $clients = User::where('role', 'client')->count();

        $appUp = Cache::get('app:health:last-state', 'up') === 'up';
        $integratorUp = Cache::get('marzpay:health:last-state', 'unknown') === 'up';

        return [
            Stat::make(' Wallet Balance', 'UGX ' . number_format($walletBalance))
                ->description('Total funds currently in account')
                ->descriptionIcon('heroicon-o-wallet')
                ->color('success'),

            Stat::make('Evently Total Income', 'UGX ' . number_format($totalCommission))
                ->description('Your 10% earned (All time)')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('primary'),

            Stat::make('Provider Balances Owed', 'UGX ' . number_format($providerOwed))
                ->description('Their 90% waiting to be withdrawn')
                ->descriptionIcon('heroicon-o-clock')
                ->color('warning'),

            Stat::make('Total Withdrawn', 'UGX ' . number_format($totalWithdrawn))
                ->description('Payouts sent to providers')
                ->descriptionIcon('heroicon-o-arrow-up-tray')
                ->color('danger'),

            Stat::make('Successful Payments', 'UGX ' . number_format($filteredSuccessful))
                ->description('Processed in selected dates')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('info'),

            Stat::make('Failed Payments', $filteredFailedCount)
                ->description('Failed in selected dates')
                ->descriptionIcon('heroicon-o-x-circle')
                ->color('danger'),

            Stat::make('Registered Clients', $clients)
                ->description('Looking for services')
                ->descriptionIcon('heroicon-o-users')
                ->color('gray'),

            Stat::make('Verified Providers', $verifiedProviders)
                ->description('Active on platform')
                ->descriptionIcon('heroicon-o-check-badge')
                ->color('gray'),

            Stat::make('App Status', $appUp ? 'Online' : 'Offline')
                ->description('API health check')
                ->descriptionIcon($appUp ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                ->color($appUp ? 'success' : 'danger'),

            Stat::make('Integrator Status', $integratorUp ? 'Online' : 'Offline')
                ->description('NylonPay connection')
                ->descriptionIcon($integratorUp ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                ->color($integratorUp ? 'success' : 'danger'),
        ];
    }
}

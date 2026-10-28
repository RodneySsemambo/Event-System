<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use App\Models\ProviderProfile;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class BookingsProvidersChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected ?string $heading = 'Providers vs Bookings';
    protected ?string $subheading = 'New Providers and Bookings over time';
    protected ?string $description = 'This chart shows the number of new providers and bookings over time';
    protected  ?string $maxHeight = '300px';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $start = $this->filters['startDate'] ?? now()->startOfMonth();
        $end = $this->filters['endDate'] ?? now();

        // New Providers over time
        $providers = ProviderProfile::whereBetween('created_at', [$start, $end])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // New Bookings over time
        $bookings = Booking::whereBetween('created_at', [$start, $end])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Merge dates to ensure the chart aligns
        $allDates = $providers->pluck('date')->merge($bookings->pluck('date'))->unique()->sort()->values();

        return [
            'datasets' => [
                [
                    'label' => 'New Bookings',
                    'data' => $allDates->map(fn($date) => $bookings->firstWhere('date', $date)?->count ?? 0),
                    'borderColor' => '#00288E', // Deep Blue
                    'backgroundColor' => 'rgba(0, 40, 142, 0.2)',
                    'tension' => 0.4,
                ],
                [
                    'label' => 'New Providers',
                    'data' => $allDates->map(fn($date) => $providers->firstWhere('date', $date)?->count ?? 0),
                    'borderColor' => '#2b7351', // Green
                    'backgroundColor' => 'rgba(43, 115, 81, 0.2)',
                    'tension' => 0.4,
                ],
            ],
            'labels' => $allDates->map(fn($d) => \Carbon\Carbon::parse($d)->format('M d')),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}

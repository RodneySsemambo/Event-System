<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class RevenueChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 2;
    protected  ?string $heading = 'Evently Revenue (10% Commission)';
    protected  ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $start = $this->filters['startDate'] ?? now()->startOfMonth();
        $end = $this->filters['endDate'] ?? now();

        $bookings = Booking::where('status', 'completed')
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('DATE(created_at) as date, SUM(commission_amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Revenue (UGX)',
                    'data' => $bookings->pluck('total')->toArray(),
                    'borderColor' => '#C9A227', // Evently Gold
                    'backgroundColor' => 'rgba(201, 162, 39, 0.2)',
                    'fill' => true,
                    'tension' => 0.4, // Smooth lines
                ],
            ],
            'labels' => $bookings->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('M d'))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}

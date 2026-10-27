<?php

namespace App\Filament\Pages;

use App\Models\Booking;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Schema;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    public function filtersForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('startDate')
                    ->label('Start Date')
                    ->live()
                    ->default(now()->startOfMonth()),
                DatePicker::make('endDate')
                    ->label('End Date')
                    ->live()
                    ->default(now()),

            ]);
    }

    protected function getHeaderActions(): array
    {
        return [

            Action::make('export_csv')
                ->label('Export Bookings CSV')
                ->icon('heroicon-m-arrow-down-tray')
                ->color('success')
                ->action(function () {
                    $start = $this->filters['startDate'] ?? now()->startOfMonth();
                    $end = $this->filters['endDate'] ?? now();

                    $bookings = Booking::with(['clientProfile.user', 'providerProfile'])
                        ->whereBetween('created_at', [$start, $end])
                        ->get();

                    $headers = [
                        'Content-Type' => 'text/csv',
                        'Content-Disposition' => 'attachment; filename="evently_report.csv"',
                    ];

                    return response()->stream(function () use ($bookings) {
                        $file = fopen('php://output', 'w');
                        fputcsv($file, ['ID', 'Client', 'Provider', 'Event', 'Amount', 'Commission', 'Status', 'Date']);
                        foreach ($bookings as $b) {
                            fputcsv($file, [
                                $b->id,
                                $b->clientProfile?->user?->name,
                                $b->providerProfile?->business_name,
                                $b->event_type,
                                $b->quoted_amount,
                                $b->commission_amount,
                                $b->status,
                                $b->created_at->format('Y-m-d'),
                            ]);
                        }
                        fclose($file);
                    }, 200, $headers);
                }),
        ];
    }
}

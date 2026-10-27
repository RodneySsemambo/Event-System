<?php

namespace App\Filament\Pages;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Withdrawal;
use BackedEnum;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use UnitEnum;

class Reports extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-document-chart-bar';
    protected string $view = 'filament.pages.reports';
    protected static ?int $navigationSort = 5;
    protected static string |UnitEnum|null $navigationGroup = 'Reports';


    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'startDate' => now()->startOfMonth()->format('Y-m-d'),
            'endDate' => now()->format('Y-m-d'),
            'report_type' => 'bookings',
            'format' => 'csv',
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                DatePicker::make('startDate')->required()->live(),
                DatePicker::make('endDate')->required()->live(),
                Select::make('report_type')
                    ->options([
                        'bookings' => 'Bookings Report',
                        'payments' => 'Payments Report',
                        'withdrawals' => 'Withdrawals Report',
                    ])->required()->live(),
                Select::make('format')
                    ->label('Download Format')
                    ->options([
                        'csv' => 'CSV Spreadsheet',
                        'pdf' => 'PDF Document',
                    ])->required(),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('download')
                ->label('Download Report')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(fn() => $this->download()),
        ];
    }

    public function download()
    {
        $start = $this->data['startDate'];
        $end = $this->data['endDate'];
        $type = $this->data['report_type'];
        $format = $this->data['format'];

        // Fetch Data
        if ($type === 'bookings') {
            $data = Booking::with(['clientProfile.user', 'providerProfile'])->whereBetween('created_at', [$start, $end])->get();
            $title = "Bookings Report";
        } elseif ($type === 'payments') {
            $data = Payment::whereBetween('created_at', [$start, $end])->get();
            $title = "Payments Report";
        } else {
            $data = Withdrawal::whereBetween('created_at', [$start, $end])->get();
            $title = "Withdrawals Report";
        }

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('reports.pdf', compact('data', 'type', 'start', 'end', 'title'));
            $pdf->setPaper('a4', 'landscape');
            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->stream();
            }, "{$type}_report_{$start}_to_{$end}.pdf");
        }

        // CSV fallback
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$type}_report_{$start}_to_{$end}.csv\"",
        ];

        return response()->stream(function () use ($data, $type) {
            $file = fopen('php://output', 'w');

            if ($type === 'bookings') {
                fputcsv($file, ['ID', 'Client', 'Provider', 'Event', 'Amount', 'Commission', 'Status', 'Date']);
                foreach ($data as $row) {
                    fputcsv($file, [
                        $row->id,
                        $row->clientProfile?->user?->name,
                        $row->providerProfile?->business_name,
                        $row->event_type,
                        $row->quoted_amount,
                        $row->commission_amount,
                        $row->status,
                        $row->created_at->format('Y-m-d')
                    ]);
                }
            } elseif ($type === 'payments') {
                fputcsv($file, ['ID', 'Booking ID', 'Amount', 'Phone', 'Status', 'Reference', 'Date']);
                foreach ($data as $row) {
                    fputcsv($file, [$row->id, $row->booking_id, $row->amount, $row->phone_number, $row->status, $row->marzpay_reference, $row->created_at->format('Y-m-d')]);
                }
            } else {
                fputcsv($file, ['ID', 'Provider ID', 'Amount', 'Phone', 'Status', 'Date']);
                foreach ($data as $row) {
                    fputcsv($file, [$row->id, $row->provider_profile_id, $row->amount, $row->phone_number, $row->status, $row->created_at->format('Y-m-d')]);
                }
            }
            fclose($file);
        }, 200, $headers);
    }
}

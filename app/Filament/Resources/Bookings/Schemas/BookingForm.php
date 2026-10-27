<?php

namespace App\Filament\Resources\Bookings\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class BookingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('id')->disabled(),
                TextInput::make('event_type')->disabled(),
                TextInput::make('clientProfile.user.name')->label('Client')->disabled(),
                TextInput::make('providerProfile.business_name')->label('Provider')->disabled(),
                TextInput::make('event_date')->disabled(),
                TextInput::make('quoted_amount')->prefix('UGX')->disabled(),
                TextInput::make('commission_amount')->prefix('UGX')->label('Evently Commission')->disabled(),
                TextInput::make('provider_payout')->prefix('UGX')->label('Provider Payout')->disabled(),
                Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'accepted' => 'Accepted',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ])->disabled(),
            ]);
    }
}

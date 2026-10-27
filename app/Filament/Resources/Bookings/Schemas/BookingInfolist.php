<?php

namespace App\Filament\Resources\Bookings\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class BookingInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('client_profile_id')
                    ->numeric(),
                TextEntry::make('provider_profile_id')
                    ->numeric(),
                TextEntry::make('event_type'),
                TextEntry::make('event_date')
                    ->date(),
                TextEntry::make('location'),
                TextEntry::make('guest_count')
                    ->numeric(),
                TextEntry::make('quoted_amount')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('commission_amount')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('provider_payout')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('notes')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}

<?php

namespace App\Filament\Resources\Payments\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PaymentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('booking_id')
                    ->numeric(),
                TextEntry::make('client_profile_id')
                    ->numeric(),
                TextEntry::make('provider_profile_id')
                    ->numeric(),
                TextEntry::make('amount')
                    ->numeric(),
                TextEntry::make('currency'),
                TextEntry::make('phone_number'),
                TextEntry::make('marzpay_reference'),
                TextEntry::make('marzpay_uuid')
                    ->placeholder('-'),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('failure_reason')
                    ->placeholder('-'),
                TextEntry::make('type')
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

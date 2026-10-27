<?php

namespace App\Filament\Resources\Withdrawals\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class WithdrawalInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('provider_profile_id')
                    ->numeric(),
                TextEntry::make('amount')
                    ->numeric(),
                TextEntry::make('phone_number'),
                TextEntry::make('status'),
                TextEntry::make('marzpay_reference')
                    ->placeholder('-'),
                TextEntry::make('failure_reason')
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}

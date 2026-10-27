<?php

namespace App\Filament\Resources\Withdrawals\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class WithdrawalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('provider_profile_id')
                    ->required()
                    ->numeric(),
                TextInput::make('amount')
                    ->required()
                    ->numeric(),
                TextInput::make('phone_number')
                    ->tel()
                    ->required(),
                TextInput::make('status')
                    ->required()
                    ->default('pending'),
                TextInput::make('marzpay_reference'),
                TextInput::make('failure_reason'),
                TextInput::make('raw_payload'),
            ]);
    }
}

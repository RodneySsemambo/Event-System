<?php

namespace App\Filament\Resources\Payments\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('booking_id')
                    ->required()
                    ->numeric(),
                TextInput::make('client_profile_id')
                    ->required()
                    ->numeric(),
                TextInput::make('provider_profile_id')
                    ->required()
                    ->numeric(),
                TextInput::make('amount')
                    ->required()
                    ->numeric(),
                TextInput::make('currency')
                    ->required()
                    ->default('UGX'),
                TextInput::make('phone_number')
                    ->tel()
                    ->required(),
                TextInput::make('marzpay_reference')
                    ->required(),
                TextInput::make('marzpay_uuid'),
                Select::make('status')
                    ->options([
            'pending' => 'Pending',
            'successful' => 'Successful',
            'failed' => 'Failed',
            'refunded' => 'Refunded',
        ])
                    ->default('pending')
                    ->required(),
                TextInput::make('failure_reason'),
                Select::make('type')
                    ->options(['collection' => 'Collection', 'refund' => 'Refund'])
                    ->default('collection')
                    ->required(),
                TextInput::make('raw_payload'),
            ]);
    }
}

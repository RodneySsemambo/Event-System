<?php

namespace App\Filament\Resources\ProviderProfiles\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ProviderProfileForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required()
                    ->searchable(),
                TextInput::make('business_name')->required(),
                Select::make('category_id')
                    ->relationship('category', 'name')
                    ->required(),
                TextInput::make('location')->required(),
                Select::make('verification_status')
                    ->options([
                        'unverified' => 'Unverified',
                        'verified' => 'Verified',
                    ])
                    ->required()
                    ->default('unverified'),
                TextInput::make('whatsapp_number'),
                TextInput::make('price_range_min')->numeric()->prefix('UGX'),
                TextInput::make('price_range_max')->numeric()->prefix('UGX'),
            ]);
    }
}

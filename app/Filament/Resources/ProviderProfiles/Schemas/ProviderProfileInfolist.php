<?php

namespace App\Filament\Resources\ProviderProfiles\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ProviderProfileInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('user_id')
                    ->numeric(),
                TextEntry::make('category_id')
                    ->numeric(),
                TextEntry::make('business_name'),
                TextEntry::make('location'),
                TextEntry::make('whatsapp_number')
                    ->placeholder('-'),
                TextEntry::make('bio')
                    ->placeholder('-'),
                TextEntry::make('profile_photo_path')
                    ->placeholder('-'),
                TextEntry::make('banner_path')
                    ->placeholder('-'),
                TextEntry::make('price_range_min')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('price_range_max')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('verification_status')
                    ->badge(),
                TextEntry::make('availability_status')
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

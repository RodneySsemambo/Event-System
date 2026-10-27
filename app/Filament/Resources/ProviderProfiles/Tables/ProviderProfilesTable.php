<?php

namespace App\Filament\Resources\ProviderProfiles\Tables;

use App\Models\ProviderProfile;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProviderProfilesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('business_name')->searchable(),
                TextColumn::make('user.name')->label('Owner')->searchable(),
                TextColumn::make('category.name')->label('Category'),
                TextColumn::make('verification_status')
                    ->badge()
                    ->color(fn(string $state): string => $state === 'verified' ? 'success' : 'gray'),
                TextColumn::make('created_at')->date(),
            ])
            ->filters([
                SelectFilter::make('verification_status')
                    ->options([
                        'verified' => 'Verified',
                        'unverified' => 'Unverified',
                    ]),
            ])
            ->actions([
                EditAction::make(),
                // Add a quick "Verify" button to the table row
                Action::make('verify')
                    ->action(fn(ProviderProfile $record) => $record->update(['verification_status' => 'verified']))
                    ->color('success')
                    ->icon('heroicon-o-check-badge')
                    ->requiresConfirmation()
                    ->hidden(fn(ProviderProfile $record) => $record->verification_status === 'verified'),
            ]);
    }
}

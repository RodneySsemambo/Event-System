<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('name')->searchable(),
                TextColumn::make('email')->searchable(),
                TextColumn::make('role')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'admin' => 'danger',
                        'provider' => 'warning',
                        'client' => 'success',
                        'user' => 'primary',
                        'manager' => 'secondary',
                        'secretary' => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->options([
                        'client' => 'Clients',
                        'provider' => 'Providers',
                        'admin' => 'Admins',
                        'user' => 'Users',
                        'manager' => 'Managers',
                        'secretary' => 'Secretaries',
                    ]),
            ]);
    }
}

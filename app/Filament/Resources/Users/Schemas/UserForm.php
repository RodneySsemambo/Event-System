<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Spatie\Permission\Models\Role;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),

                TextInput::make('password')
                    ->password()
                    ->dehydrated(fn($state) => filled($state))
                    ->required(fn(string $context): bool => $context === 'create'),

                Select::make('role')
                    ->options([
                        'client' => 'Client',
                        'provider' => 'Provider',
                        'admin' => 'Admin',
                    ])
                    ->required()
                    ->default('client')
                    ->helperText('This is the app role (controls what they can do on the main website).'),

                CheckboxList::make('roles')
                    ->relationship('roles', 'name')
                    ->columns(2)
                    ->label('Admin Panel Roles (Shield)')
                    ->helperText('This grants access to the /admin dashboard.'),
            ]);
    }
}

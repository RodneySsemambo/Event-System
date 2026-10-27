<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

#[Fillable([
    'name',
    'email',
    'password',
    'google_id',
    'avatar',
    'role',
    'status',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasRoles;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        // Strictly check if the database column is 'admin'
        // OR if they have the Spatie role 'super_admin'
        if ($this->role === 'admin') {
            return true;
        }

        if ($this->hasRole('super_admin')) {
            return true;
        }

        return false;
    }

    //-----Relationships----------------

    public function clientProfile()
    {
        return $this->hasOne(ClientProfile::class);
    }

    public function providerProfile()
    {
        return $this->hasOne(ProviderProfile::class);
    }

    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_user_id');
    }

    public function reportsFiled()
    {
        return $this->hasMany(Report::class, 'reporter_user_id');
    }

    // --- Role helpers --------------------------------------------------

    public function isClient(): bool
    {
        return $this->role === 'client';
    }

    public function isProvider(): bool
    {
        return $this->role === 'provider';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function routeNotificationForAfricasTalking($notification)
    {
        return $this->providerProfile?->whatsapp_number
            ?? $this->clientProfile?->phone
            ?? $this->phone
            ?? null;
    }
}

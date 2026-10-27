<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_profile_id',
        'provider_profile_id',
        'event_type',
        'event_date',
        'location',
        'guest_count',
        'notes',
        'status',
        'quoted_amount',
        'commission_amount',
        'provider_payout',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
        ];
    }

    public function clientProfile()
    {
        return $this->belongsTo(ClientProfile::class);
    }

    public function providerProfile()
    {
        return $this->belongsTo(ProviderProfile::class);
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }
    public function payment()
    {
        return $this->hasOne(Payment::class);
    }
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeForProviderId(Builder $query, int $providerProfileId): Builder
    {
        return $query->where('provider_profile_id', $providerProfileId);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}

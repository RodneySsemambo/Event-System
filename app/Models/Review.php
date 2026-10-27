<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'client_profile_id',
        'provider_profile_id',
        'rating',
        'comment'
    ];

    //-----Relationships---------------

    public function booking()

    {
        return $this->belongsTo(Booking::class);
    }

    public function clientProfile()
    {
        return $this->belongsTo(ClientProfile::class);
    }

    public function providerProfile()
    {
        return $this->belongsTo(ProviderProfile::class);
    }
}

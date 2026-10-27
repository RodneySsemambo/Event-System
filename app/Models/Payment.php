<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Override;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'client_profile_id',
        'provider_profile_id',
        'amount',
        'currency',
        'phone_number',
        'marzpay_reference',
        'status',
        'type',
        'raw_payload',
    ];


    protected function casts()
    {
        return [
            'amount' => 'decimal:2',
            'raw_payload' => 'array'
        ];
    }

    //------Relationships------------

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

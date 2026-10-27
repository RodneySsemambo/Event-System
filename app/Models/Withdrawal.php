<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Withdrawal extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_profile_id',
        'amount',
        'phone_number',
        'status',
        'marzpay_reference',
        'failure_reason',
        'raw_payload',
    ];

    protected function casts(): array
    {
        return [
            'amount'      => 'decimal:2',
            'raw_payload' => 'array',
        ];
    }

    public function providerProfile()
    {
        return $this->belongsTo(ProviderProfile::class);
    }
}

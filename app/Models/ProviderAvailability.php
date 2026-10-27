<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProviderAvailability extends Model
{
    use HasFactory;
    protected $fillable = [

        'provider_profile_id',
        'date',
        'is_available'

    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'is_available' => 'boolean',
        ];
    }

    public function providerProfile()
    {
        return $this->belongsTo(ProviderProfile::class);
    }
}

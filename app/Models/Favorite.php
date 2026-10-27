<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    protected $fillable = [
        'client_profile_id',
        'provider_profile_id'
    ];

    public function providerProfile()
    {
        return $this->belongsTo(ProviderProfile::class);
    }
}

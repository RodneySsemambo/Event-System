<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PortfolioItem extends Model
{
    use HasFactory;
    protected $fillable = [

        'provider_profile_id',
        'image_path',
        'media_type',
        'caption',
        'sort_order'
    ];

    public function providerProfile()
    {
        return $this->belongsTo(ProviderProfile::class);
    }
}

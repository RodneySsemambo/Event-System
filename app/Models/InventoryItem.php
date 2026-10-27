<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Override;

class InventoryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_profile_id',
        'category_id',
        'name',
        'description',
        'rental_price',
        'quantity_available',
        'image_path',
    ];

    protected function casts()
    {
        return [
            'rental_price' => 'decimal:2'
        ];
    }

    //-----Relationships-------------------

    public function providerProfile()
    {
        return $this->belongsTo(ProviderProfile::class);
    }

    public function rentalOrders()
    {
        return $this->hasMany(RentalOrder::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}

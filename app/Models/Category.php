<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description'
    ];


    //------Relationships----------------

    public function providerProfile()
    {
        return $this->hasMany(ProviderProfile::class);
    }

    public function inventoryItems()
    {
        return $this->hasMany(InventoryItem::class);
    }
}

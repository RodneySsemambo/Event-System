<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Override;

class RentalOrder extends Model
{
    use HasFactory;
    protected $fillable = [
        'client_profile_id',
        'inventory_item_id',
        'start_date',
        'end_date',
        'quantity',
        'status',
        'notes',
    ];

    public function casts()
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    //-----Relationships------------

    public function clientProfile()
    {
        return $this->belongsTo(ClientProfile::class);
    }

    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'phone',
        'default_location'
    ];

    //-----Relationships-----------------
    public function user()
    {

        return $this->belongsTo(User::class);
    }

    public function bookings()
    {

        return $this->hasMany(Booking::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function conversations()
    {
        return $this->hasMany(Conversation::class);
    }

    public function rentalOrders()
    {
        return $this->hasMany(RentalOrder::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }
}

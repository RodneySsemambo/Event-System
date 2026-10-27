<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_profile_id',
        'provider_profile_id'
    ];

    //----Relationships------------

    public function clientProfile()
    {
        return $this->belongsTo(ClientProfile::class);
    }

    public function providerProfile()
    {
        return $this->belongsTo(providerProfile::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class)->orderBy('sent_at');
    }

    public function latestMessage()
    {

        return $this->hasOne(Message::class)->latestOfMany('sent_at');
    }
}

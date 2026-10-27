<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'sender_user_id',
        'body',
        'sent_at',
        'read_at'

    ];

    protected function casts()
    {
        return [
            'sent_at' => 'datetime',
            'read_at' => 'datetime'
        ];
    }

    //----Relationships---------
    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }
}

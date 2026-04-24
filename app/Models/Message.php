<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'order_id',
        'sender_id',
        'content',
        'attachment_path',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function getSenderNameAttribute()
    {
        return $this->sender->name;
    }

    public function getSenderRoleAttribute()
    {
        return $this->sender->role;
    }
}

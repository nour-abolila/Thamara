<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'id',
        'type',
        'notifiable_type',
        'notifiable_id',
        'data',
        'read_at'
    ];


    public function user()
    {
        return $this->belongsTo(User::class, 'notifiable_id');
    }

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];
}

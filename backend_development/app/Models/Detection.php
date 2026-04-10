<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Detection extends Model
{
    protected $fillable = [
        'user_id',
        'plant_name',
        'image_path',
        'disease_name',
        'disease_description',
        'confidence',
        'severity_level',
        'treatment',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

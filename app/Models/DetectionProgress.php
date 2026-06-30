<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetectionProgress extends Model
{
    protected $fillable = ['detection_id', 'image_path', 'progress_status', 'progress_level', 'confidence_level'];


    public function detection()
    {
        return $this->belongsTo(Detection::class);
    }
}

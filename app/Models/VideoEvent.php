<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VideoEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'video_id',
        'event',
        'referrer',
        'user_agent',
        'ip',
    ];

    public function video()
    {
        return $this->belongsTo(Video::class);
    }
}


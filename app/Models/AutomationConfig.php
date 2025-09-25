<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutomationConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'channel_id', 'client_id', 'webhook_url', 'secret', 'last_response', 'synced_at',
    ];

    protected $casts = [
        'last_response' => 'array',
        'synced_at' => 'datetime',
    ];

    public function channel(): BelongsTo
    {
        return $this->belongsTo(NotificationChannel::class, 'channel_id');
    }
}


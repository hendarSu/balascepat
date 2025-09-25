<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Broadcast extends Model
{
    use HasFactory;

    protected $fillable = [
        'name','text','channel_id','group_id','client_id','external_id','recipients','recipients_count','response','sent_at',
    ];

    protected $casts = [
        'recipients' => 'array',
        'response' => 'array',
        'sent_at' => 'datetime',
    ];

    public function channel(): BelongsTo
    {
        return $this->belongsTo(NotificationChannel::class, 'channel_id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(CustomerGroup::class, 'group_id');
    }
}

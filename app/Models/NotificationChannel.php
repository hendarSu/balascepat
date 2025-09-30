<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class NotificationChannel extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'name',
        'base_url',
        'headers_key',
        'headers_value',
        'auth_type',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForCurrentUser($query)
    {
        return $query->where('user_id', auth()->id());
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}

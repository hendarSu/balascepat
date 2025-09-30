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
        'n8n_username',
        'n8n_password',
    ];

    // Do not auto-cast encrypt to allow backward compatibility with plain values
    // Provide a helper to safely decrypt when needed.

    public function getDecryptedN8nPassword(): ?string
    {
        $raw = $this->getRawOriginal('n8n_password');
        if (!$raw) {
            return null;
        }
        try {
            return \Illuminate\Support\Facades\Crypt::decryptString($raw);
        } catch (\Throwable $e) {
            // Fallback for legacy plain-text records
            return $raw;
        }
    }

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

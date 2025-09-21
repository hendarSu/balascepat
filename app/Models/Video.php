<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class Video extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'original_filename',
        'file_path',
        'file_size',
        'mime_type',
        'status',
        'is_public',
        'hls_path',
        'encryption_type',
        'encryption_keys',
        'export_token',
        'allow_export',
        'allow_embed',
        'export_expires_at',
        'embed_settings',
        'view_count',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'allow_export' => 'boolean',
        'allow_embed' => 'boolean',
        'encryption_keys' => 'array',
        'embed_settings' => 'array',
        'export_expires_at' => 'datetime',
        'view_count' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function generateExportToken(): void
    {
        $this->export_token = Str::random(40);
        $this->save();
    }

    public function isExportValid(): bool
    {
        if (!$this->export_token) {
            return false;
        }

        if ($this->export_expires_at && $this->export_expires_at->isPast()) {
            return false;
        }

        // Export is valid if either export or embed is allowed
        return (bool) ($this->allow_export || $this->allow_embed);
    }

    public function isEmbedAllowedForDomain(?string $domain): bool
    {
        $settings = $this->embed_settings ?? [];
        $allowed = $settings['allowed_domains'] ?? [];
        if (empty($allowed)) {
            return true; // no restriction
        }

        $domain = strtolower((string) $domain);
        foreach ($allowed as $rule) {
            $rule = strtolower(trim($rule));
            if ($rule === $domain) {
                return true;
            }
            // wildcard support: *.example.com
            if (str_starts_with($rule, '*.') && str_ends_with($domain, substr($rule, 1))) {
                return true;
            }
        }
        return false;
    }

    public function getPlaylistUrl(): string
    {
        return route('video.playlist', ['playlist' => basename((string) $this->hls_path)]);
    }

    public function getExportUrl(): ?string
    {
        if (!$this->export_token) {
            return null;
        }
        return route('video.export.show', ['token' => $this->export_token]);
    }

    public function getEmbedUrl(): ?string
    {
        if (!$this->export_token) {
            return null;
        }
        return route('video.embed.show', ['token' => $this->export_token]);
    }

    public function incrementViewCount(): void
    {
        $this->view_count = ($this->view_count ?? 0) + 1;
        $this->save();
    }
}


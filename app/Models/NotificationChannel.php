<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationChannel extends Model
{
    protected $fillable = [
        'name',
        'base_url',
        'headers_key',
        'headers_value',
        'auth_type',
    ];
}

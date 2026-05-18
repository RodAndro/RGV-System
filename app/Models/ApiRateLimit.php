<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiRateLimit extends Model
{
    protected $fillable = [
        'user_id',
        'tier',
        'key',
        'ip_address',
        'limit_per_minute',
        'remaining',
        'blocked',
        'reset_at',
    ];

    protected $casts = [
        'blocked' => 'boolean',
        'reset_at' => 'datetime',
    ];
}

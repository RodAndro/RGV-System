<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QueryPerformanceLog extends Model
{
    protected $fillable = [
        'name',
        'duration_ms',
        'rows_returned',
        'cache_hit',
        'context',
    ];

    protected $casts = [
        'cache_hit' => 'boolean',
        'context' => 'array',
    ];
}

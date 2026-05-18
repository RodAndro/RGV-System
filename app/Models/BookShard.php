<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookShard extends Model
{
    protected $fillable = [
        'shard_key',
        'connection',
        'range_start',
        'range_end',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}

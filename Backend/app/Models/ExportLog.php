<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExportLog extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'format',
        'status',
        'filters',
        'columns',
        'record_count',
        'file_path',
        'failure_message',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'filters' => 'array',
        'columns' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];
}

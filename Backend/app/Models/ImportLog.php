<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportLog extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'file_name',
        'status',
        'total_rows',
        'processed_rows',
        'successful_rows',
        'failed_rows',
        'duplicate_strategy',
        'errors',
        'error_report_path',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'errors' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function progress(): int
    {
        if ($this->total_rows === 0) {
            return 0;
        }

        return min(100, (int) round(($this->processed_rows / $this->total_rows) * 100));
    }
}

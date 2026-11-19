<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduledTask extends Model
{
    protected $fillable = [
        'command',
        'status',
        'started_at',
        'finished_at',
        'duration_ms',
        'output',
        'failure_message',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public static function start(string $command): self
    {
        return static::create([
            'command' => $command,
            'status' => 'started',
            'started_at' => now(),
        ]);
    }

    public function finish(string $status = 'success', ?string $output = null, ?string $failure = null): void
    {
        $finishedAt = now();

        $this->update([
            'status' => $status,
            'finished_at' => $finishedAt,
            'duration_ms' => $this->started_at ? $this->started_at->diffInMilliseconds($finishedAt) : null,
            'output' => $output,
            'failure_message' => $failure,
        ]);
    }
}

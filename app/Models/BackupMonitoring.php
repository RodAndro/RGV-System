<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class BackupMonitoring extends Model
{
    protected $table = 'backup_monitoring';

    protected $fillable = [
        'disk',
        'status',
        'file_path',
        'size_bytes',
        'checksum',
        'message',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function verifyChecksum(): ?bool
    {
        if (!$this->file_path || !$this->checksum) {
            return null;
        }

        $disk = Storage::disk('local');

        if (!$disk->exists($this->file_path)) {
            return null;
        }

        return hash_equals($this->checksum, hash_file('sha256', $disk->path($this->file_path)));
    }

    public function getSizeFormattedAttribute(): string
    {
        if (!$this->size_bytes) {
            return '—';
        }

        $mb = $this->size_bytes / 1024 / 1024;

        return $mb >= 1000
            ? round($mb / 1024, 1) . ' GB'
            : round($mb, 1) . ' MB';
    }
}

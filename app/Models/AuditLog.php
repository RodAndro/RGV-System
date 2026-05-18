<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'event',
        'auditable_type',
        'auditable_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'url',
        'checksum',
        'previous_checksum',
        'archived_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'archived_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function auditable()
    {
        return $this->morphTo();
    }

    public function scopeNotArchived($query)
    {
        return $query->whereNull('archived_at');
    }

    public function scopeArchived($query)
    {
        return $query->whereNotNull('archived_at');
    }

    public function isChecksumValid(): bool
    {
        return hash_equals($this->checksum, self::makeChecksum($this->payloadForChecksum()));
    }

    protected static function booted(): void
    {
        static::creating(function (AuditLog $log) {
            $log->previous_checksum = static::query()->latest('id')->value('checksum');
            $log->checksum = self::makeChecksum($log->payloadForChecksum());
        });
    }

    public static function makeChecksum(array $payload): string
    {
        return hash_hmac('sha256', json_encode($payload, JSON_UNESCAPED_SLASHES), config('app.key'));
    }

    private function payloadForChecksum(): array
    {
        return [
            'user_id' => $this->user_id,
            'event' => $this->event,
            'auditable_type' => $this->auditable_type,
            'auditable_id' => $this->auditable_id,
            'old_values' => $this->old_values,
            'new_values' => $this->new_values,
            'ip_address' => $this->ip_address,
            'url' => $this->url,
            'previous_checksum' => $this->previous_checksum,
        ];
    }
}

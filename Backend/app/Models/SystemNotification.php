<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemNotification extends Model
{
    use HasFactory;

    protected $table = 'notifications';

    protected $fillable = [
        'user_id',
        'type',
        'data',
        'title',
        'message',
        'link',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function getDataAttribute(): array
    {
        return [
            'type' => $this->type,
            'title' => $this->title,
            'message' => $this->message,
            'link' => $this->link,
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function markAsRead()
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    public function setDataAttribute(array $data): void
    {
        $this->attributes['type'] = $data['type'] ?? $this->attributes['type'] ?? 'system';
        $this->attributes['title'] = $data['title'] ?? $this->attributes['title'] ?? 'Notification';
        $this->attributes['message'] = $data['message'] ?? $this->attributes['message'] ?? '';
        $this->attributes['link'] = $data['link'] ?? $this->attributes['link'] ?? null;
    }
}

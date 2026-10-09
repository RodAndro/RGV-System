<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Booking extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'reference_number',
        'full_name',
        'email',
        'contact_number',
        'address',
        'preferred_date',
        'preferred_time',
        'purpose_category',
        'reason',
        'status',
        'employee_id',
        'remarks',
        'approved_at',
        'rejected_at',
        'completed_at',
        'cancelled_at',
    ];

    protected $casts = [
        'preferred_date' => 'date',
        'preferred_time' => 'datetime:H:i',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function scopePending(Builder $query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved(Builder $query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeCompleted(Builder $query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeForDate(Builder $query, string $date)
    {
        return $query->where('preferred_date', $date);
    }

    public function getPreferredTimeFormattedAttribute(): ?string
    {
        $preferredTime = $this->attributes['preferred_time'] ?? null;

        return $preferredTime ? Carbon::parse($preferredTime)->format('g:i A') : null;
    }

    public function getPreferredTime24HourAttribute(): ?string
    {
        $preferredTime = $this->attributes['preferred_time'] ?? null;

        return $preferredTime ? Carbon::parse($preferredTime)->format('H:i:s') : null;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($booking) {
            if (empty($booking->reference_number)) {
                $booking->reference_number = 'BK-' . strtoupper(uniqid());
            }
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'employee_id', 'remarks'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}

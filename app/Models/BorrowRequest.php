<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class BorrowRequest extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'request_number',
        'employee_id',
        'approved_by',
        'status',
        'reason',
        'borrow_date',
        'due_date',
        'return_date',
        'penalty_notes',
        'admin_remarks',
        'approved_at',
        'rejected_at',
        'borrowed_at',
        'returned_at',
    ];

    protected $casts = [
        'borrow_date' => 'date',
        'due_date' => 'date',
        'return_date' => 'date',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'borrowed_at' => 'datetime',
        'returned_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function borrowItems()
    {
        return $this->hasMany(BorrowItem::class, 'borrow_request_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeBorrowed($query)
    {
        return $query->where('status', 'borrowed');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'borrowed')
            ->where('due_date', '<', now());
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($request) {
            if (empty($request->request_number)) {
                $request->request_number = 'BR-' . strtoupper(uniqid());
            }
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'approved_by', 'return_date'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}

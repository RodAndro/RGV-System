<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class BorrowItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'borrow_request_id',
        'inventory_id',
        'quantity',
        'condition_borrowed',
        'condition_returned',
        'is_returned',
        'returned_at',
        'damage_notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'is_returned' => 'boolean',
        'returned_at' => 'datetime',
    ];

    public function borrowRequest()
    {
        return $this->belongsTo(BorrowRequest::class, 'borrow_request_id');
    }

    public function inventory()
    {
        return $this->belongsTo(Inventory::class, 'inventory_id');
    }

    public function scopeReturned(Builder $query)
    {
        return $query->where('is_returned', true);
    }

    public function scopeNotReturned(Builder $query)
    {
        return $query->where('is_returned', false);
    }
}

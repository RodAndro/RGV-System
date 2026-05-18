<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Inventory extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'item_code',
        'name',
        'description',
        'category_id',
        'supplier_id',
        'quantity',
        'unit',
        'unit_cost',
        'status',
        'condition',
        'location',
        'image_path',
        'low_stock_threshold',
        'date_added',
        'is_active',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_cost' => 'decimal:2',
        'low_stock_threshold' => 'integer',
        'date_added' => 'date',
        'is_active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(InventoryCategory::class, 'category_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function borrowItems()
    {
        return $this->hasMany(BorrowItem::class, 'inventory_id');
    }

    public function scopeAvailable(Builder $query)
    {
        return $query->where('status', 'available');
    }

    public function scopeLowStock(Builder $query)
    {
        return $query->whereColumn('quantity', '<=', 'low_stock_threshold');
    }

    public function scopeActive(Builder $query)
    {
        return $query->where('is_active', true);
    }

    public function isLowStock()
    {
        return $this->quantity <= $this->low_stock_threshold;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['quantity', 'status', 'condition'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}

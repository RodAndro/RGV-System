<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Inventory extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'item_code',
        'name',
        'brand',
        'description',
        'category_id',
        'quantity',
        'unit',
        'unit_cost',
        'status',
        'condition',
        'location',
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

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Inventory $inventory): void {
            $inventory->qr_token = $inventory->generateUniqueQrToken();
        });

        static::updating(function (Inventory $inventory): void {
            if ($inventory->isDirty('qr_token')) {
                throw new \LogicException('An inventory QR token cannot be changed.');
            }
        });
    }

    /**
     * Immutable QR identifier. It is never mass-assignable and never
     * derived from mutable fields, so a rendered QR always resolves
     * to the same inventory item.
     */
    public function generateUniqueQrToken(): string
    {
        do {
            $token = 'QR-'.Str::upper(Str::random(32));
        } while (self::where('qr_token', $token)->exists());

        return $token;
    }

    public function qrUrl(): string
    {
        return route('inventory.qr.show', ['token' => $this->qr_token]);
    }

    public function category()
    {
        return $this->belongsTo(InventoryCategory::class, 'category_id');
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

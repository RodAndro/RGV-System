<?php

namespace App\Models;

use Database\Factories\BookFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    /** @use HasFactory<BookFactory> */
    use HasFactory;

    protected $fillable = [
        'isbn',
        'isbn13',
        'title',
        'slug',
        'category_id',
        'author',
        'publisher',
        'format',
        'price',
        'stock',
        'sales_count',
        'rating',
        'description',
        'is_active',
        'published_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'rating' => 'decimal:2',
        'is_active' => 'boolean',
        'published_at' => 'date',
    ];

    public function category()
    {
        return $this->belongsTo(InventoryCategory::class, 'category_id');
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'isbn13' => $this->isbn13,
            'title' => $this->title,
            'author' => $this->author,
            'description' => $this->description,
            'category_id' => $this->category_id,
            'is_active' => $this->is_active,
        ];
    }
}

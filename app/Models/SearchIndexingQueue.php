<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SearchIndexingQueue extends Model
{
    protected $table = 'search_indexing_queue';

    protected $fillable = [
        'book_id',
        'status',
        'attempts',
        'failure_message',
        'indexed_at',
    ];

    protected $casts = [
        'indexed_at' => 'datetime',
    ];
}

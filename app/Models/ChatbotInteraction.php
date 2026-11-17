<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotInteraction extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'question_hash',
        'page_type',
        'source_refs',
        'response',
        'model_name',
        'response_time_ms',
        'outcome',
        'created_at',
    ];

    protected function casts(): array
    {
        return ['source_refs' => 'array', 'created_at' => 'datetime'];
    }
}

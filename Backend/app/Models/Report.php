<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_number',
        'generated_by',
        'type',
        'title',
        'summary',
        'data',
        'file_path',
        'file_format',
        'report_date',
        'start_date',
        'end_date',
        'is_ai_generated',
    ];

    protected $casts = [
        'data' => 'array',
        'report_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_ai_generated' => 'boolean',
    ];

    public function generatedBy()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeAiGenerated($query)
    {
        return $query->where('is_ai_generated', true);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($report) {
            if (empty($report->report_number)) {
                $report->report_number = 'RPT-' . strtoupper(uniqid());
            }
        });
    }
}

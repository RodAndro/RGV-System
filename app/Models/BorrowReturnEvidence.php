<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BorrowReturnEvidence extends Model
{
    protected $table = 'borrow_return_evidences';

    /**
     * Legacy return-evidence metadata retained only for backward compatibility.
     * The current mobile return flow does not upload files to Google Drive.
     */
    protected $fillable = [
        'borrow_request_id',
        'idempotency_key',
        'file_name',
        'mime_type',
        'uploaded_by',
        'uploaded_at',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
    ];

    public function borrowRequest(): BelongsTo
    {
        return $this->belongsTo(BorrowRequest::class, 'borrow_request_id');
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function borrowItems(): HasMany
    {
        return $this->hasMany(BorrowItem::class, 'return_evidence_id');
    }
}

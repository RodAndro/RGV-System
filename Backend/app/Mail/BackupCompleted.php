<?php

namespace App\Mail;

use App\Models\BackupMonitoring;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BackupCompleted extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public BackupMonitoring $monitor,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[' . config('app.name') . '] Backup Completed Successfully',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.backup-completed',
            with: [
                'disk' => $this->monitor->disk,
                'size' => $this->monitor->size_bytes
                    ? round($this->monitor->size_bytes / 1024 / 1024, 1) . ' MB'
                    : 'unknown',
                'completedAt' => $this->monitor->completed_at?->format('M d, Y - g:i A'),
                'checksum' => $this->monitor->checksum,
            ],
        );
    }
}

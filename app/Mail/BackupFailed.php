<?php

namespace App\Mail;

use App\Models\BackupMonitoring;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BackupFailed extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public BackupMonitoring $monitor,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[' . config('app.name') . '] BACKUP FAILED — Action Required',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.backup-failed',
            with: [
                'disk' => $this->monitor->disk,
                'error' => $this->monitor->message,
                'failedAt' => $this->monitor->completed_at?->format('M d, Y - g:i A'),
            ],
        );
    }
}

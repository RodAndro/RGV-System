<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EntityUpdateNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $entityType,
        public readonly string $entityName,
        public readonly string $action,
        public readonly string $details,
        public readonly ?string $link = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[RGV] {$this->action}: {$this->entityType} — {$this->entityName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.entity-update',
        );
    }
}

<?php

namespace App\Mail;

use App\Models\Report;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReportGenerated extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Report $report,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[' . config('app.name') . '] ' . $this->report->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.report-generated',
            with: [
                'title' => $this->report->title,
                'summary' => $this->report->summary,
                'type' => $this->report->type,
                'reportDate' => $this->report->report_date?->format('M d, Y'),
            ],
        );
    }
}

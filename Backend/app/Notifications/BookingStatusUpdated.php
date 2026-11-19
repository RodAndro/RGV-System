<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingStatusUpdated extends Notification
{
    use Queueable;
    use RespectsPreferences;

    public function __construct(
        public Booking $booking,
        public string $status,
    ) {}

    public function via(object $notifiable): array
    {
        return $this->channelsViaPreferences($notifiable, 'booking');
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Work Request '.ucfirst($this->status).' - RGV Multi-Tech Services')
            ->greeting('Dear '.$notifiable->name.',')
            ->line('The work request assigned to you is now '.$this->status.'.')
            ->line('Reference Number: '.$this->booking->reference_number)
            ->line('Customer: '.$this->booking->full_name)
            ->line('Scheduled Date: '.$this->booking->preferred_date->format('F d, Y'));

        if ($this->status === 'rejected' && $this->booking->remarks) {
            $mail->line('Remarks: '.$this->booking->remarks);
        }

        return $mail->action('View Work Request', route('employee.bookings.show', $this->booking));
    }

    public function toArray(object $notifiable): array
    {
        $message = 'Assigned work request '.$this->booking->reference_number.' is now '.$this->status.'.';
        if ($this->status === 'rejected' && $this->booking->remarks) {
            $message .= ' Remarks: '.$this->booking->remarks;
        }

        return [
            'type' => 'booking',
            'title' => 'Work Request '.ucfirst($this->status),
            'message' => $message,
            'link' => route('employee.bookings.show', $this->booking),
        ];
    }
}

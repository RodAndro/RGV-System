<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewBookingReceived extends Notification
{
    use Queueable;
    use RespectsPreferences;

    public $booking;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }

    public function via(object $notifiable): array
    {
        return $this->channelsViaPreferences($notifiable, 'booking');
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Booking Received - RGV Multi-Tech Services')
            ->greeting('Dear Admin,')
            ->line('A new booking has been received.')
            ->line('Reference Number: ' . $this->booking->reference_number)
            ->line('Client: ' . $this->booking->full_name)
            ->line('Email: ' . $this->booking->email)
            ->line('Phone: ' . $this->booking->phone)
            ->line('Date: ' . $this->booking->preferred_date->format('F d, Y'))
            ->line('Time: ' . $this->booking->preferred_time)
            ->line('Please review and approve or reject the booking.')
            ->action('View Booking', route('admin.bookings.show', $this->booking))
            ->line('Thank you!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'booking',
            'title' => 'New Booking Received',
            'message' => 'New booking from ' . $this->booking->full_name . ' for ' . $this->booking->preferred_date->format('F d, Y'),
            'link' => route('admin.bookings.show', $this->booking),
        ];
    }
}

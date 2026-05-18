<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingCompleted extends Notification
{
    use Queueable;

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
            ->subject('Booking Completed - RGV Multi-Tech Services')
            ->greeting('Dear ' . $this->booking->full_name . ',')
            ->line('Your booking has been completed successfully!')
            ->line('Reference Number: ' . $this->booking->reference_number)
            ->line('Date: ' . $this->booking->preferred_date->format('F d, Y'))
            ->line('Time: ' . $this->booking->preferred_time)
            ->line('Thank you for choosing RGV Multi-Tech Services!')
            ->action('Track Booking', route('booking.track', $this->booking->reference_number));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'booking',
            'title' => 'Booking Completed',
            'message' => 'Your booking has been completed successfully.',
            'link' => route('booking.track', $this->booking->reference_number),
        ];
    }
}

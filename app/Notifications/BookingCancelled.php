<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingCancelled extends Notification
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
            ->subject('Booking Cancelled - RGV Multi-Tech Services')
            ->greeting('Dear ' . $this->booking->full_name . ',')
            ->line('Your booking has been cancelled.')
            ->line('Reference Number: ' . $this->booking->reference_number)
            ->line('Date: ' . $this->booking->preferred_date->format('F d, Y'))
            ->line('Time: ' . $this->booking->preferred_time)
            ->line('Please contact us if you have any questions or would like to reschedule.')
            ->action('Track Booking', route('booking.track', $this->booking->reference_number))
            ->line('Thank you for considering RGV Multi-Tech Services!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'booking',
            'title' => 'Booking Cancelled',
            'message' => 'Your booking has been cancelled.',
            'link' => route('booking.track', $this->booking->reference_number),
        ];
    }
}

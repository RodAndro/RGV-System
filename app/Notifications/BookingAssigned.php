<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingAssigned extends Notification
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
            ->subject('New Booking Assigned - RGV Multi-Tech Services')
            ->greeting('Dear ' . $notifiable->name . ',')
            ->line('You have been assigned to a new booking.')
            ->line('Reference Number: ' . $this->booking->reference_number)
            ->line('Client: ' . $this->booking->full_name)
            ->line('Date: ' . $this->booking->preferred_date->format('F d, Y'))
            ->line('Time: ' . $this->booking->preferred_time)
            ->line('Please review the booking details and prepare accordingly.')
            ->action('View Booking', route('employee.bookings.show', $this->booking))
            ->line('Thank you!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'booking',
            'title' => 'New Booking Assigned',
            'message' => 'You have been assigned to booking #' . $this->booking->reference_number,
            'link' => route('admin.bookings.show', $this->booking),
        ];
    }
}

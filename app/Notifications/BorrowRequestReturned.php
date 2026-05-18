<?php

namespace App\Notifications;

use App\Models\BorrowRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BorrowRequestReturned extends Notification
{
    use Queueable;
    use RespectsPreferences;

    public $borrowRequest;

    public function __construct(BorrowRequest $borrowRequest)
    {
        $this->borrowRequest = $borrowRequest;
    }

    public function via(object $notifiable): array
    {
        return $this->channelsViaPreferences($notifiable, 'borrow_request');
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Items Returned - RGV Multi-Tech Services')
            ->greeting('Dear ' . $notifiable->name . ',')
            ->line('Your borrowed items have been successfully returned.')
            ->line('Request Number: ' . $this->borrowRequest->request_number)
            ->line('Return Date: ' . $this->borrowRequest->returned_at->format('F d, Y'))
            ->line('Thank you for returning the items on time.')
            ->action('View Request', route('employee.borrow-requests.show', $this->borrowRequest))
            ->line('Thank you for using RGV Multi-Tech Services!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'borrow_request',
            'title' => 'Items Returned',
            'message' => 'Your borrowed items have been successfully returned.',
            'link' => route('employee.borrow-requests.show', $this->borrowRequest),
        ];
    }
}

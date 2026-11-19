<?php

namespace App\Notifications;

use App\Models\BorrowRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BorrowRequestCancelled extends Notification
{
    use Queueable;
    use RespectsPreferences;

    public function __construct(public BorrowRequest $borrowRequest) {}

    public function via(object $notifiable): array
    {
        return $this->channelsViaPreferences($notifiable, 'borrow_request');
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Borrow Request Cancelled - RGV Multi-Tech Services')
            ->greeting('Dear '.$notifiable->name.',')
            ->line('Your pending borrow request has been cancelled.')
            ->line('Request Number: '.$this->borrowRequest->request_number)
            ->line('The reserved items are available again.')
            ->action('View Request', route('employee.borrow-requests.show', $this->borrowRequest));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'borrow_request',
            'title' => 'Borrow Request Cancelled',
            'message' => 'Your pending borrow request '.$this->borrowRequest->request_number.' was cancelled. Reserved items are available again.',
            'link' => route('employee.borrow-requests.show', $this->borrowRequest),
        ];
    }
}

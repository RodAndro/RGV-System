<?php

namespace App\Notifications;

use App\Models\BorrowRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BorrowRequestBorrowed extends Notification
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
            ->subject('Items Borrowed - RGV Multi-Tech Services')
            ->greeting('Dear ' . $notifiable->name . ',')
            ->line('Your borrowed items have been successfully marked as borrowed.')
            ->line('Request Number: ' . $this->borrowRequest->request_number)
            ->line('Borrow Date: ' . $this->borrowRequest->borrow_date->format('F d, Y'))
            ->line('Due Date: ' . $this->borrowRequest->due_date->format('F d, Y'))
            ->line('Please ensure to return the items on or before the due date.')
            ->action('View Request', route('employee.borrow-requests.show', $this->borrowRequest))
            ->line('Thank you for using RGV Multi-Tech Services!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'borrow_request',
            'title' => 'Items Borrowed',
            'message' => 'Your borrowed items have been marked as borrowed. Due date: ' . $this->borrowRequest->due_date->format('F d, Y'),
            'link' => route('employee.borrow-requests.show', $this->borrowRequest),
        ];
    }
}

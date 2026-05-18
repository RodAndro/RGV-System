<?php

namespace App\Notifications;

use App\Models\BorrowRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BorrowRequestApproved extends Notification
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
            ->subject('Borrow Request Approved - RGV Multi-Tech Services')
            ->greeting('Dear ' . $notifiable->name . ',')
            ->line('Your borrow request has been approved!')
            ->line('Request Number: ' . $this->borrowRequest->request_number)
            ->line('Borrow Date: ' . $this->borrowRequest->borrow_date->format('F d, Y'))
            ->line('Due Date: ' . $this->borrowRequest->due_date->format('F d, Y'))
            ->line('Please collect the items from the inventory department.')
            ->action('View Request', route('employee.borrow-requests.show', $this->borrowRequest))
            ->line('Thank you for using RGV Multi-Tech Services!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'borrow_request',
            'title' => 'Borrow Request Approved',
            'message' => 'Your borrow request has been approved. Due date: ' . $this->borrowRequest->due_date->format('F d, Y'),
            'link' => route('employee.borrow-requests.show', $this->borrowRequest),
        ];
    }
}

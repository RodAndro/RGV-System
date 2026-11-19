<?php

namespace App\Notifications;

use App\Models\BorrowRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewBorrowRequestReceived extends Notification
{
    use Queueable;
    use RespectsPreferences;

    public BorrowRequest $borrowRequest;

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
            ->subject('New Borrow Request Received - RGV Multi-Tech Services')
            ->greeting('Dear Admin,')
            ->line('A new borrow request has been received.')
            ->line('Request Number: ' . $this->borrowRequest->request_number)
            ->line('Employee: ' . $this->borrowRequest->employee->name)
            ->line('Borrow Date: ' . $this->borrowRequest->borrow_date->format('F d, Y'))
            ->line('Due Date: ' . $this->borrowRequest->due_date->format('F d, Y'))
            ->line('Reason: ' . $this->borrowRequest->reason)
            ->line('Please review and approve or reject the request.')
            ->action('View Request', route('admin.borrow-requests.show', $this->borrowRequest))
            ->line('Thank you!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'borrow_request',
            'title' => 'New Borrow Request Received',
            'message' => 'New borrow request from ' . $this->borrowRequest->employee->name . ' for ' . $this->borrowRequest->borrow_date->format('F d, Y'),
            'link' => route('admin.borrow-requests.show', $this->borrowRequest),
        ];
    }
}

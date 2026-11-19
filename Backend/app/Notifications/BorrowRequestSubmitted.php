<?php

namespace App\Notifications;

use App\Models\BorrowRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BorrowRequestSubmitted extends Notification
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
            ->subject('Borrow Request Submitted - RGV Multi-Tech Services')
            ->greeting('Dear '.$notifiable->name.',')
            ->line('Your borrow request has been submitted and is pending admin review.')
            ->line('Request Number: '.$this->borrowRequest->request_number)
            ->line('Borrow Date: '.$this->borrowRequest->borrow_date->format('F d, Y'))
            ->line('Due Date: '.$this->borrowRequest->due_date->format('F d, Y'))
            ->action('View Request', route('employee.borrow-requests.show', $this->borrowRequest));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'borrow_request',
            'title' => 'Borrow Request Submitted',
            'message' => 'Your borrow request '.$this->borrowRequest->request_number.' was submitted and is pending admin review.',
            'link' => route('employee.borrow-requests.show', $this->borrowRequest),
        ];
    }
}

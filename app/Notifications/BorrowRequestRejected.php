<?php

namespace App\Notifications;

use App\Models\BorrowRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BorrowRequestRejected extends Notification
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
            ->subject('Borrow Request Rejected - RGV Multi-Tech Services')
            ->greeting('Dear ' . $notifiable->name . ',')
            ->line('We regret to inform you that your borrow request has been rejected.')
            ->line('Request Number: ' . $this->borrowRequest->request_number)
            ->line('Reason: ' . $this->borrowRequest->admin_remarks)
            ->line('Please contact the admin if you have any questions.')
            ->action('View Request', route('employee.borrow-requests.show', $this->borrowRequest))
            ->line('Thank you for using RGV Multi-Tech Services!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'borrow_request',
            'title' => 'Borrow Request Rejected',
            'message' => 'Your borrow request has been rejected. Reason: ' . $this->borrowRequest->admin_remarks,
            'link' => route('employee.borrow-requests.show', $this->borrowRequest),
        ];
    }
}

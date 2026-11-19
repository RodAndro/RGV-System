<?php

namespace App\Notifications;

use App\Models\BorrowRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BorrowRequestItemsReturned extends Notification
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
            ->subject('Return Recorded - RGV Multi-Tech Services')
            ->greeting('Dear '.$notifiable->name.',')
            ->line('The returned items for borrow request '.$this->borrowRequest->request_number.' were recorded.')
            ->line('Any remaining items are still listed on your request.')
            ->action('View Request', route('employee.borrow-requests.show', $this->borrowRequest));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'borrow_request',
            'title' => 'Return Recorded',
            'message' => 'Returned items for borrow request '.$this->borrowRequest->request_number.' were recorded. Any remaining items are still listed on your request.',
            'link' => route('employee.borrow-requests.show', $this->borrowRequest),
        ];
    }
}

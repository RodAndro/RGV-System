<?php

namespace App\Notifications;

use App\Models\BorrowRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmployeeBorrowRequestActivity extends Notification
{
    use Queueable;
    use RespectsPreferences;

    public function __construct(
        public BorrowRequest $borrowRequest,
        public string $activity,
    ) {}

    public function via(object $notifiable): array
    {
        return $this->channelsViaPreferences($notifiable, 'borrow_request');
    }

    public function toMail(object $notifiable): MailMessage
    {
        [$title, $message] = $this->content();

        return (new MailMessage)
            ->subject($title.' - RGV Multi-Tech Services')
            ->greeting('Dear '.$notifiable->name.',')
            ->line($message)
            ->line('Request Number: '.$this->borrowRequest->request_number)
            ->line('Employee: '.($this->borrowRequest->employee?->name ?? 'Employee'))
            ->action('View Request', route('admin.borrow-requests.show', $this->borrowRequest));
    }

    public function toArray(object $notifiable): array
    {
        [$title, $message] = $this->content();

        return [
            'type' => 'borrow_request',
            'title' => $title,
            'message' => $message,
            'link' => route('admin.borrow-requests.show', $this->borrowRequest),
        ];
    }

    private function content(): array
    {
        $employee = $this->borrowRequest->employee?->name ?? 'An employee';
        $requestNumber = $this->borrowRequest->request_number;

        return match ($this->activity) {
            'cancelled' => [
                'Borrow Request Cancelled',
                $employee.' cancelled borrow request '.$requestNumber.'.',
            ],
            'borrowed' => [
                'Items Marked as Borrowed',
                $employee.' marked items from borrow request '.$requestNumber.' as borrowed.',
            ],
            'returned' => [
                'Items Returned',
                $employee.' returned items for borrow request '.$requestNumber.'.',
            ],
            default => [
                'Borrow Request Updated',
                $employee.' updated borrow request '.$requestNumber.'.',
            ],
        };
    }
}

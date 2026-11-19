<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmployeeStatusUpdated extends Notification
{
    use Queueable;
    use RespectsPreferences;

    public function __construct(
        public User $employee,
        public bool $isActive,
        public ?User $changedBy = null,
    ) {}

    public function via(object $notifiable): array
    {
        return $this->channelsViaPreferences($notifiable, 'user');
    }

    public function toMail(object $notifiable): MailMessage
    {
        $status = $this->isActive ? 'activated' : 'deactivated';

        return (new MailMessage)
            ->subject('Employee Account '.ucfirst($status).' - RGV Multi-Tech Services')
            ->greeting('Dear '.$notifiable->name.',')
            ->line($this->employee->name.' employee account was '.$status.'.')
            ->line('Updated by: '.($this->changedBy?->name ?? 'Administrator'))
            ->action('View Employee', route('admin.users.show', $this->employee))
            ->line('This notification was sent to administrators for account-status tracking.');
    }

    public function toArray(object $notifiable): array
    {
        $status = $this->isActive ? 'activated' : 'deactivated';

        return [
            'type' => 'user',
            'title' => 'Employee Account '.ucfirst($status),
            'message' => $this->employee->name.' employee account was '.$status.'.',
            'link' => route('admin.users.show', $this->employee),
        ];
    }
}

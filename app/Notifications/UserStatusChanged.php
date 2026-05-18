<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserStatusChanged extends Notification
{
    use Queueable;
    use RespectsPreferences;

    public $user;
    public $isActive;

    public function __construct(User $user, $isActive)
    {
        $this->user = $user;
        $this->isActive = $isActive;
    }

    public function via(object $notifiable): array
    {
        return $this->channelsViaPreferences($notifiable, 'user');
    }

    public function toMail(object $notifiable): MailMessage
    {
        $status = $this->isActive ? 'activated' : 'deactivated';
        
        return (new MailMessage)
            ->subject('Account Status ' . ucfirst($status) . ' - RGV Multi-Tech Services')
            ->greeting('Dear ' . $this->user->name . ',')
            ->line('Your account has been ' . $status . '.')
            ->line($this->isActive 
                ? 'You can now access your account and use all the features.'
                : 'Your account has been temporarily disabled. Please contact the administrator for more information.')
            ->action('Login', url('/login'))
            ->line('Thank you for using RGV Multi-Tech Services!');
    }

    public function toArray(object $notifiable): array
    {
        $status = $this->isActive ? 'activated' : 'deactivated';
        
        return [
            'type' => 'user',
            'title' => 'Account ' . ucfirst($status),
            'message' => 'Your account has been ' . $status . '.',
            'link' => url('/login'),
        ];
    }
}

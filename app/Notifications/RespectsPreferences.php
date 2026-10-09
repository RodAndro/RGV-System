<?php

namespace App\Notifications;

use Illuminate\Notifications\AnonymousNotifiable;

trait RespectsPreferences
{
    protected function channelsViaPreferences(object $notifiable, string $type): array
    {
        $emailEnabled = env('NOTIFICATION_EMAIL_ENABLED', 'true') === 'true';

        if ($notifiable instanceof AnonymousNotifiable) {
            return $emailEnabled ? ['mail'] : [];
        }

        $channels = [];
        $pref = method_exists($notifiable, 'notificationPreferences')
            ? $notifiable->notificationPreferences()->where('type', $type)->first()
            : null;

        if ($emailEnabled && (!$pref || $pref->email_enabled)) {
            $channels[] = 'mail';
        }
        if (!$pref || $pref->in_app_enabled) {
            $channels[] = 'database';
        }

        return $channels;
    }
}

<?php

namespace App\Notifications;

trait RespectsPreferences
{
    protected function channelsViaPreferences(object $notifiable, string $type): array
    {
        $channels = [];
        $pref = method_exists($notifiable, 'notificationPreferences')
            ? $notifiable->notificationPreferences()->where('type', $type)->first()
            : null;

        if (!$pref || $pref->email_enabled) {
            $channels[] = 'mail';
        }
        if (!$pref || $pref->in_app_enabled) {
            $channels[] = 'database';
        }

        return $channels;
    }
}

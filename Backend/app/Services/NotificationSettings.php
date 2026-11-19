<?php

namespace App\Services;

class NotificationSettings
{
    public static function emailEnabled(): bool
    {
        return filter_var(
            env('NOTIFICATION_EMAIL_ENABLED', true),
            FILTER_VALIDATE_BOOLEAN,
            FILTER_NULL_ON_FAILURE,
        ) ?? true;
    }
}
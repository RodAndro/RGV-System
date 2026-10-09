<?php

namespace App\Observers;

use App\Mail\EntityUpdateNotification;
use App\Models\Booking;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class BookingObserver
{
    public function saved(Booking $booking): void
    {
        Cache::forget('admin.stats.bookings');
    }

    public function deleted(Booking $booking): void
    {
        Cache::forget('admin.stats.bookings');
    }

    public function restored(Booking $booking): void
    {
        Cache::forget('admin.stats.bookings');
    }

    private function adminEmail(): ?string
    {
        if (env('NOTIFICATION_EMAIL_ENABLED', 'true') !== 'true') {
            return null;
        }

        return env('ADMIN_NOTIFICATION_EMAIL') ?: env('BACKUP_NOTIFICATION_EMAIL') ?: env('SYSTEM_NOTIFICATION_EMAIL');
    }

    public function updated(Booking $booking): void
    {
        $email = $this->adminEmail();
        if (!$email) {
            return;
        }

        if ($booking->wasChanged('status')) {
            $action = 'Status Changed';
            $details = "Booking {$booking->reference_number} status changed from \"{$booking->getOriginal('status')}\" to \"{$booking->status}\" for {$booking->full_name}.";

            Mail::to($email)->queue(new EntityUpdateNotification(
                entityType: 'Booking',
                entityName: $booking->reference_number,
                action: $action,
                details: $details,
                link: route('admin.bookings.show', $booking),
            ));
        }

        if ($booking->wasChanged('employee_id') && $booking->employee_id) {
            $action = 'Employee Assigned';
            $details = "Employee assigned to booking {$booking->reference_number} for {$booking->full_name}.";

            Mail::to($email)->queue(new EntityUpdateNotification(
                entityType: 'Booking',
                entityName: $booking->reference_number,
                action: $action,
                details: $details,
                link: route('admin.bookings.show', $booking),
            ));
        }
    }
}

<?php

namespace App\Observers;

use App\Mail\EntityUpdateNotification;
use App\Models\BorrowRequest;
use Illuminate\Support\Facades\Mail;

class BorrowRequestObserver
{
    private function adminEmail(): ?string
    {
        if (env('NOTIFICATION_EMAIL_ENABLED', 'true') !== 'true') {
            return null;
        }

        return env('ADMIN_NOTIFICATION_EMAIL') ?: env('BACKUP_NOTIFICATION_EMAIL');
    }

    public function updated(BorrowRequest $borrowRequest): void
    {
        $email = $this->adminEmail();
        if (!$email) {
            return;
        }

        if ($borrowRequest->isDirty('status')) {
            $borrowerName = $borrowRequest->employee ? $borrowRequest->employee->name : 'Unknown';
            $action = 'Status Changed';
            $details = "Borrow request {$borrowRequest->request_number} status changed from \"{$borrowRequest->getOriginal('status')}\" to \"{$borrowRequest->status}\" for {$borrowerName}.";

            Mail::to($email)->queue(new EntityUpdateNotification(
                entityType: 'Borrow Request',
                entityName: "{$borrowRequest->request_number} — {$borrowerName}",
                action: $action,
                details: $details,
                link: route('admin.borrow-requests.show', $borrowRequest),
            ));
        }
    }
}

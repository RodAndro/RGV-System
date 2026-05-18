<?php

namespace App\Observers;

use App\Mail\EntityUpdateNotification;
use App\Models\Inventory;
use Illuminate\Support\Facades\Mail;

class InventoryObserver
{
    private function adminEmail(): ?string
    {
        if (env('NOTIFICATION_EMAIL_ENABLED', 'true') !== 'true') {
            return null;
        }

        return env('ADMIN_NOTIFICATION_EMAIL') ?: env('BACKUP_NOTIFICATION_EMAIL');
    }

    public function created(Inventory $inventory): void
    {
        $email = $this->adminEmail();
        if (!$email) {
            return;
        }

        Mail::to($email)->queue(new EntityUpdateNotification(
            entityType: 'Inventory',
            entityName: $inventory->name,
            action: 'New Item Created',
            details: "New inventory item \"{$inventory->name}\" (Code: {$inventory->item_code}) added with {$inventory->quantity} {$inventory->unit}.",
            link: route('admin.inventories.show', $inventory),
        ));
    }

    public function updated(Inventory $inventory): void
    {
        $email = $this->adminEmail();
        if (!$email) {
            return;
        }

        $changes = $inventory->getChanges();
        unset($changes['updated_at']);

        if (empty($changes)) {
            return;
        }

        $changedFields = implode(', ', array_keys($changes));
        $details = "Inventory item \"{$inventory->name}\" (Code: {$inventory->item_code}) updated. Changed fields: {$changedFields}.";

        Mail::to($email)->queue(new EntityUpdateNotification(
            entityType: 'Inventory',
            entityName: $inventory->name,
            action: 'Item Updated',
            details: $details,
            link: route('admin.inventories.show', $inventory),
        ));
    }

    public function deleted(Inventory $inventory): void
    {
        $email = $this->adminEmail();
        if (!$email) {
            return;
        }

        Mail::to($email)->queue(new EntityUpdateNotification(
            entityType: 'Inventory',
            entityName: $inventory->name,
            action: 'Item Deleted',
            details: "Inventory item \"{$inventory->name}\" (Code: {$inventory->item_code}) has been deleted.",
        ));
    }
}

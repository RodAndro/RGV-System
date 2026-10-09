<?php

namespace App\Observers;

use App\Mail\EntityUpdateNotification;
use App\Models\Inventory;
use App\Models\User;
use App\Notifications\LowStockAlert;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class InventoryObserver
{
    private function adminEmail(): ?string
    {
        if (env('NOTIFICATION_EMAIL_ENABLED', 'true') !== 'true') {
            return null;
        }

        return env('ADMIN_NOTIFICATION_EMAIL') ?: env('BACKUP_NOTIFICATION_EMAIL') ?: env('SYSTEM_NOTIFICATION_EMAIL');
    }

    public function created(Inventory $inventory): void
    {
        if ($inventory->isLowStock()) {
            $this->notifyAdminsOfLowStock($inventory);
        }

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
        if ($inventory->wasChanged(['quantity', 'low_stock_threshold'])) {
            $wasLowStock = (int) $inventory->getOriginal('quantity') <= (int) $inventory->getOriginal('low_stock_threshold');
            if (! $wasLowStock && $inventory->isLowStock()) {
                $this->notifyAdminsOfLowStock($inventory);
            }
        }

        $email = $this->adminEmail();
        if (!$email) {
            return;
        }

        $changes = $inventory->getChanges();
        unset($changes['updated_at'], $changes['quantity']);

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

    private function notifyAdminsOfLowStock(Inventory $inventory): void
    {
        DB::afterCommit(function () use ($inventory): void {
            $inventory = $inventory->fresh();
            if (! $inventory || ! $inventory->isLowStock()) {
                return;
            }

            User::role('admin')->get()->each(
                fn (User $admin) => $admin->notify(new LowStockAlert($inventory)),
            );
        });
    }
}

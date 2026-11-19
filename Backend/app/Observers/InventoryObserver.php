<?php

namespace App\Observers;

use App\Mail\EntityUpdateNotification;
use App\Models\Inventory;
use App\Models\User;
use App\Notifications\LowStockAlert;
use App\Services\NotificationSettings;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class InventoryObserver
{
    private function adminEmail(): ?string
    {
        if (!NotificationSettings::emailEnabled()) {
            return null;
        }

        return env('ADMIN_NOTIFICATION_EMAIL') ?: env('BACKUP_NOTIFICATION_EMAIL') ?: env('SYSTEM_NOTIFICATION_EMAIL');
    }

    public function created(Inventory $inventory): void
    {
        $this->forgetInventoryDashboardCache();

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
        $this->forgetInventoryDashboardCache();

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
        $this->forgetInventoryDashboardCache();

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

    public function restored(Inventory $inventory): void
    {
        $this->forgetInventoryDashboardCache();
    }

    private function forgetInventoryDashboardCache(): void
    {
        DB::afterCommit(function (): void {
            Cache::forget('admin.dashboard.index');
            Cache::forget('admin.stats.inventory');
        });
    }

    private function notifyAdminsOfLowStock(Inventory $inventory): void
    {
        DB::afterCommit(function () use ($inventory): void {
            $inventory = $inventory->fresh();
            if (! $inventory || ! $inventory->isLowStock()) {
                return;
            }

            $recipients = User::role('admin')->get()
                ->merge(User::role('employee')->active()->get())
                ->unique('id');

            $recipients->each(
                fn (User $recipient) => $recipient->notify(new LowStockAlert($inventory)),
            );
        });
    }
}

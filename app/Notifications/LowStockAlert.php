<?php

namespace App\Notifications;

use App\Models\Inventory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LowStockAlert extends Notification
{
    use Queueable;
    use RespectsPreferences;

    public $inventory;

    public function __construct(Inventory $inventory)
    {
        $this->inventory = $inventory;
    }

    public function via(object $notifiable): array
    {
        return $this->channelsViaPreferences($notifiable, 'inventory');
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Low Stock Alert - RGV Multi-Tech Services')
            ->greeting('Dear Admin,')
            ->line('An inventory item has reached low stock level.')
            ->line('Item: ' . $this->inventory->name)
            ->line('Item Code: ' . $this->inventory->item_code)
            ->line('Current Quantity: ' . $this->inventory->quantity)
            ->line('Low Stock Threshold: ' . $this->inventory->low_stock_threshold)
            ->line('Please restock this item soon.')
            ->action('View Inventory', route('admin.inventories.show', $this->inventory))
            ->line('Thank you!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'inventory',
            'title' => 'Low Stock Alert',
            'message' => $this->inventory->name . ' is running low on stock. Current: ' . $this->inventory->quantity,
            'link' => route('admin.inventories.show', $this->inventory),
        ];
    }
}

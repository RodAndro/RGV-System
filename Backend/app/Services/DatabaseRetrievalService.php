<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BorrowRequest;
use App\Models\Inventory;
use App\Models\User;

/** Retrieves only the aggregate report facts exposed to the admin chatbot. */
class DatabaseRetrievalService
{
    /**
     * Read-only aggregate retrieval. No writes are performed here.
     */
    public function retrieve(string $pageType): array
    {
        return match ($pageType) {
            'bookings' => [
                'total_bookings' => Booking::query()->count(),
                'pending_bookings' => Booking::query()->where('status', 'pending')->count(),
                'approved_bookings' => Booking::query()->where('status', 'approved')->count(),
                'completed_bookings' => Booking::query()->where('status', 'completed')->count(),
                'rejected_bookings' => Booking::query()->where('status', 'rejected')->count(),
                'cancelled_bookings' => Booking::query()->where('status', 'cancelled')->count(),
            ],
            'inventory' => [
                'total_items' => Inventory::query()->count(),
                'available_items' => Inventory::query()->where('status', 'available')->count(),
                'borrowed_items' => Inventory::query()->where('status', 'borrowed')->count(),
                'maintenance_items' => Inventory::query()->where('status', 'maintenance')->count(),
                'damaged_items' => Inventory::query()->where('status', 'damaged')->count(),
                'total_quantity' => (int) Inventory::query()->sum('quantity'),
                'low_stock_items' => Inventory::query()->whereColumn('quantity', '<=', 'low_stock_threshold')->count(),
                'total_value' => (float) Inventory::query()->selectRaw('COALESCE(SUM(quantity * unit_cost), 0) as total_value')->value('total_value'),
                'out_of_stock' => Inventory::query()->where('quantity', 0)->count(),
            ],
            'borrow_requests' => [
                'total_requests' => BorrowRequest::query()->count(),
                'pending_requests' => BorrowRequest::query()->where('status', 'pending')->count(),
                'approved_requests' => BorrowRequest::query()->where('status', 'approved')->count(),
                'borrowed_items' => BorrowRequest::query()->where('status', 'borrowed')->count(),
                'returned_items' => BorrowRequest::query()->where('status', 'returned')->count(),
                'rejected_requests' => BorrowRequest::query()->where('status', 'rejected')->count(),
            ],
            'users' => [
                'total_users' => User::query()->count(),
                'active_users' => User::query()->where('is_active', true)->count(),
                'inactive_users' => User::query()->where('is_active', false)->count(),
                'admin_users' => User::role('admin')->count(),
                'employee_users' => User::role('employee')->count(),
                'users_today' => User::query()->whereDate('created_at', today())->count(),
            ],
            default => [
                'total_bookings' => Booking::query()->count(),
                'total_inventory' => Inventory::query()->count(),
                'total_borrow_requests' => BorrowRequest::query()->count(),
                'total_users' => User::query()->count(),
            ],
        };
    }

    public function sourceReference(string $pageType): string
    {
        return 'report:'.$pageType.':aggregate';
    }
}

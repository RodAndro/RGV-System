<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BorrowRequest;
use App\Models\Inventory;
use App\Models\InventoryCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TrashController extends Controller
{
    protected array $models = [
        'booking' => Booking::class,
        'borrow_request' => BorrowRequest::class,
        'inventory' => Inventory::class,
        'inventory_category' => InventoryCategory::class,
    ];

    public function index(Request $request)
    {
        $trashable = Cache::remember('admin.trash.counts', now()->addSeconds(20), function () {
            $counts = DB::query()
                ->selectSub(Booking::onlyTrashed()->selectRaw('COUNT(*)'), 'bookings')
                ->selectSub(BorrowRequest::onlyTrashed()->selectRaw('COUNT(*)'), 'borrow_requests')
                ->selectSub(Inventory::onlyTrashed()->selectRaw('COUNT(*)'), 'inventories')
                ->selectSub(InventoryCategory::onlyTrashed()->selectRaw('COUNT(*)'), 'inventory_categories')
                ->first();

            return [
                'Booking' => (int) $counts->bookings,
                'Borrow Request' => (int) $counts->borrow_requests,
                'Inventory' => (int) $counts->inventories,
                'Inventory Category' => (int) $counts->inventory_categories,
            ];
        });

        $type = $request->get('type', 'booking');

        $modelClass = $this->models[$type] ?? null;
        $records = $modelClass ? $modelClass::onlyTrashed()->latest()->paginate(25) : collect();

        return view('admin.trash.index', compact('trashable', 'records', 'type'));
    }

    public function restore(Request $request, string $type, int $id)
    {
        $modelClass = $this->models[$type] ?? null;
        if (!$modelClass) {
            return back()->with('error', 'Invalid record type.');
        }

        $record = $modelClass::onlyTrashed()->findOrFail($id);
        $record->restore();

        return back()->with('success', 'Record restored successfully.');
    }

    public function forceDelete(Request $request, string $type, int $id)
    {
        $modelClass = $this->models[$type] ?? null;
        if (!$modelClass) {
            return back()->with('error', 'Invalid record type.');
        }

        if (!Hash::check($request->password, auth()->user()->password)) {
            return back()->withErrors(['password' => 'The password is incorrect.']);
        }

        $record = $modelClass::onlyTrashed()->findOrFail($id);
        $record->forceDelete();

        return back()->with('success', 'Record permanently deleted.');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BorrowRequest;
use App\Models\Inventory;
use App\Models\InventoryCategory;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TrashController extends Controller
{
    protected array $models = [
        'booking' => Booking::class,
        'borrow_request' => BorrowRequest::class,
        'inventory' => Inventory::class,
        'inventory_category' => InventoryCategory::class,
        'supplier' => Supplier::class,
    ];

    public function index(Request $request)
    {
        $trashable = [
            'Booking' => Booking::onlyTrashed()->count(),
            'Borrow Request' => BorrowRequest::onlyTrashed()->count(),
            'Inventory' => Inventory::onlyTrashed()->count(),
            'Inventory Category' => InventoryCategory::onlyTrashed()->count(),
            'Supplier' => Supplier::onlyTrashed()->count(),
        ];

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

<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\InventoryCategory;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Inventory::with(['category', 'supplier'])
            ->where('status', 'available')
            ->where('quantity', '>', 0);

        // Filter by category
        if ($request->has('category') && $request->category != '') {
            $query->where('category_id', $request->category);
        }

        // Filter by search term
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('item_code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by condition
        if ($request->has('condition') && $request->condition != '') {
            $query->where('condition', $request->condition);
        }

        $perPage = in_array((int) $request->get('per_page'), [10, 25, 50, 100]) ? (int) $request->get('per_page') : 20;
        $inventories = $query->latest()->paginate($perPage)->appends($request->except('page'));
        $categories = InventoryCategory::active()->get();

        $stats = [
            'total' => Inventory::count(),
            'available' => Inventory::where('status', 'available')->where('quantity', '>', 0)->count(),
            'low_stock' => Inventory::lowStock()->count(),
        ];

        return view('employee.inventories.index', compact('inventories', 'categories', 'stats'));
    }

    public function show(Inventory $inventory)
    {
        $inventory->load(['category', 'supplier']);
        return view('employee.inventories.show', compact('inventory'));
    }

    public function lowStock(Request $request)
    {
        $query = Inventory::lowStock()->with('category');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('item_code', 'like', "%{$s}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        $perPage = in_array((int) $request->get('per_page'), [10, 25, 50, 100]) ? (int) $request->get('per_page') : 20;
        $inventories = $query->latest()->paginate($perPage)->appends($request->except('page'));
        $categories = \App\Models\InventoryCategory::active()->get();

        return view('employee.inventories.low-stock', compact('inventories', 'categories'));
    }
}

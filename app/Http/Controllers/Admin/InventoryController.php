<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessInventoryImport;
use App\Models\ImportLog;
use App\Models\Inventory;
use App\Models\InventoryCategory;
use App\Models\Supplier;
use App\Models\User;
use App\Notifications\LowStockAlert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Inventory::with(['category', 'supplier']);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('letter')) {
            $letter = $request->letter;
            $query->where('name', 'like', $letter . '%');
        }

        if ($request->filled('q')) {
            $search = '%' . $request->q . '%';
            $query->where(function ($query) use ($search) {
                $query->where('item_code', 'like', $search)
                      ->orWhere('name', 'like', $search)
                      ->orWhere('description', 'like', $search);
            });
        }

        $perPage = in_array((int) $request->get('per_page'), [10, 25, 50, 100]) ? (int) $request->get('per_page') : 20;
        $inventories = $query->latest()->paginate($perPage)->appends($request->except('page'));
        $categories = InventoryCategory::active()->get();
        $suppliers = Supplier::active()->get();
        
        return view('admin.inventories.index', compact('inventories', 'categories', 'suppliers'));
    }

    public function create()
    {
        $categories = InventoryCategory::active()->get();
        $suppliers = Supplier::active()->get();
        $categoryUnits = $this->getCategoryUnits();
        return view('admin.inventories.create', compact('categories', 'suppliers', 'categoryUnits'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'item_code' => ['required', 'string', 'max:255', Rule::unique('inventories', 'item_code')->withoutTrashed()],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:inventory_categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'quantity' => 'required|integer|min:0',
            'unit' => 'required|string',
            'unit_cost' => 'nullable|numeric|min:0',
            'status' => 'required|in:available,borrowed,maintenance,damaged',
            'condition' => 'required|in:new,good,fair,poor',
            'location' => 'nullable|string',
            'low_stock_threshold' => 'required|integer|min:0',
            'date_added' => 'required|date',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        $duplicateItem = Inventory::query()
            ->where('category_id', $request->category_id)
            ->whereRaw('LOWER(name) = ?', [strtolower($request->name)])
            ->exists();

        if ($duplicateItem) {
            return back()
                ->with('error', 'Duplicate inventory item detected. This item name already exists in the selected category.')
                ->withInput();
        }

        $data = $request->except('image');
        
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('inventories', 'public');
            $data['image_path'] = $path;
        }

        $inventory = Inventory::create($data);

        // Check if quantity is below low stock threshold and send alert
        if ($inventory->quantity <= $inventory->low_stock_threshold) {
            $admins = User::role('admin')->get();
            foreach ($admins as $admin) {
                $admin->notify(new LowStockAlert($inventory));
            }
        }

        return redirect()->route('admin.inventories.index')
            ->with('success', 'Inventory item added successfully.');
    }

    public function show(Inventory $inventory)
    {
        $inventory->load(['category', 'supplier', 'borrowItems']);
        return view('admin.inventories.show', compact('inventory'));
    }

    public function edit(Inventory $inventory)
    {
        $categories = InventoryCategory::active()->get();
        $suppliers = Supplier::active()->get();
        $categoryUnits = $this->getCategoryUnits();
        return view('admin.inventories.edit', compact('inventory', 'categories', 'suppliers', 'categoryUnits'));
    }

    public function update(Request $request, Inventory $inventory)
    {
        $request->validate([
            'item_code' => ['required', 'string', 'max:255', Rule::unique('inventories', 'item_code')->ignore($inventory->id)->withoutTrashed()],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:inventory_categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'quantity' => 'required|integer|min:0',
            'unit' => 'required|string',
            'unit_cost' => 'nullable|numeric|min:0',
            'status' => 'required|in:available,borrowed,maintenance,damaged',
            'condition' => 'required|in:new,good,fair,poor',
            'location' => 'nullable|string',
            'low_stock_threshold' => 'required|integer|min:0',
            'date_added' => 'required|date',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        $duplicateItem = Inventory::query()
            ->where('category_id', $request->category_id)
            ->whereRaw('LOWER(name) = ?', [strtolower($request->name)])
            ->whereKeyNot($inventory->id)
            ->exists();

        if ($duplicateItem) {
            return back()
                ->with('error', 'Duplicate inventory item detected. This item name already exists in the selected category.')
                ->withInput();
        }

        $data = $request->except('image');
        
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('inventories', 'public');
            $data['image_path'] = $path;
        }

        $inventory->update($data);

        // Check if quantity is below low stock threshold and send alert
        if ($inventory->quantity <= $inventory->low_stock_threshold) {
            $admins = User::role('admin')->get();
            foreach ($admins as $admin) {
                $admin->notify(new LowStockAlert($inventory));
            }
        }

        return redirect()->route('admin.inventories.index')
            ->with('success', 'Inventory item updated successfully.');
    }

    public function destroy(Inventory $inventory)
    {
        Inventory::destroy($inventory->id);
        return redirect()->route('admin.inventories.index')
            ->with('success', 'Inventory item deleted successfully.');
    }

    public function lowStock()
    {
        $inventories = Inventory::lowStock()->with(['category', 'supplier'])->get();
        return view('admin.inventories.low-stock', compact('inventories'));
    }

    public function generateQrCode(Inventory $inventory)
    {
        $qrData = [
            'item_code' => $inventory->item_code,
            'name' => $inventory->name,
            'category' => $inventory->category->name,
            'quantity' => $inventory->quantity,
            'unit' => $inventory->unit,
        ];

        $qrCode = QrCode::format('png')
            ->size(300)
            ->margin(10)
            ->generate(json_encode($qrData));

        return response($qrCode)->header('Content-Type', 'image/png');
    }

    /**
     * Download an import template CSV with headers and example row.
     */
    public function importTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="inventory-import-template.csv"',
        ];

        $columns = [
            'item_code',
            'name',
            'description',
            'category',
            'quantity',
            'unit',
            'unit_cost',
            'status',
            'condition',
            'supplier',
            'location',
            'low_stock_threshold',
            'date_added',
        ];

        $exampleRow = [
            'RGV-001',
            'Sample Item',
            'Sample description for this item',
            'Office Supplies',
            '10',
            'pcs',
            '25.50',
            'available',
            'good',
            'Supplier Name',
            'Shelf A-1',
            '5',
            now()->format('Y-m-d'),
        ];

        $callback = function () use ($columns, $exampleRow) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $columns);
            fputcsv($handle, $exampleRow);
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Import inventory items from uploaded CSV/XLSX file.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls|max:51200',
            'duplicate_strategy' => 'required|in:skip,update',
        ]);

        $path = $request->file('file')->store('imports');

        $importLog = ImportLog::create([
            'user_id' => auth()->id(),
            'type' => 'inventory',
            'file_name' => $request->file('file')->getClientOriginalName(),
            'status' => 'queued',
            'duplicate_strategy' => $request->duplicate_strategy,
            'started_at' => now(),
        ]);

        ProcessInventoryImport::dispatch($importLog->id, $path, $request->duplicate_strategy);

        return back()->with('success', 'Inventory import queued. It will process in the background.');
    }

    /**
     * Get unit options mapped by category
     * This defines the appropriate units for each category type
     */
    private function getCategoryUnits(): array
    {
        $categories = InventoryCategory::active()->get();
        
        $unitMapping = [
            'Electronics' => ['pcs', 'sets', 'boxes'],
            'Software' => ['licenses', 'pcs', 'subscriptions'],
            'Hardware' => ['pcs', 'boxes', 'kg'],
            'Office Supplies' => ['pcs', 'boxes', 'reams', 'packets'],
            'Tools' => ['pcs', 'sets', 'boxes'],
            'Materials' => ['kg', 'lbs', 'meters', 'boxes'],
            'Chemicals' => ['liters', 'kg', 'bottles', 'containers'],
        ];

        $result = [];
        foreach ($categories as $category) {
            // Check if category has a specific unit mapping
            $categoryName = $category->name;
            
            if (isset($unitMapping[$categoryName])) {
                $result[$category->id] = $unitMapping[$categoryName];
            } else {
                // Default units for unknown categories
                $result[$category->id] = ['pcs', 'kg', 'lbs', 'meters', 'liters', 'boxes', 'sets'];
            }
        }

        return $result;
    }
}

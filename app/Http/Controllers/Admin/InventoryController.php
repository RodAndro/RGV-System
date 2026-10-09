<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessInventoryImport;
use App\Models\ImportLog;
use App\Models\Inventory;
use App\Models\InventoryCategory;
use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Encoder\Encoder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Inventory::with(['category']);

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

        $perPage = in_array((int) $request->get('per_page'), [10, 25, 50, 100]) ? (int) $request->get('per_page') : 10;
        $inventories = $query->latest()->paginate($perPage)->appends($request->except('page'));
        $categories = InventoryCategory::active()->get();
        
        $stats = Cache::remember('admin.stats.inventory', now()->addSeconds(20), function () {
            $row = Inventory::query()->selectRaw("COUNT(*) as total, COUNT(CASE WHEN status = 'available' THEN 1 END) as available, COUNT(CASE WHEN status = 'borrowed' THEN 1 END) as borrowed, COUNT(CASE WHEN status = 'maintenance' THEN 1 END) as maintenance, COUNT(CASE WHEN status = 'damaged' THEN 1 END) as damaged")
                ->first();

            return array_map('intval', $row->getAttributes());
        });
        
        return view('admin.inventories.index', compact('inventories', 'categories', 'stats'));
    }

    public function create()
    {
        $categories = InventoryCategory::active()->get();
        $categoryUnits = $this->getCategoryUnits();
        return view('admin.inventories.create', compact('categories', 'categoryUnits'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'item_code' => ['required', 'string', 'max:255', Rule::unique('inventories', 'item_code')->withoutTrashed()],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:inventory_categories,id',
            'quantity' => 'required|integer|min:0',
            'unit' => 'required|string',
            'unit_cost' => 'nullable|numeric|min:0',
            'status' => 'required|in:available,borrowed,maintenance,damaged',
            'condition' => 'required|in:new,good,fair,poor',
            'location' => 'nullable|string',
            'low_stock_threshold' => 'required|integer|min:0',
            'date_added' => 'required|date',
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

        $inventory = Inventory::create($data);

        return redirect()->route('admin.inventories.index')
            ->with('success', 'Inventory item added successfully.');
    }

    public function show(Inventory $inventory)
    {
        $inventory->load(['category', 'borrowItems']);
        return view('admin.inventories.show', compact('inventory'));
    }

    public function edit(Inventory $inventory)
    {
        $categories = InventoryCategory::active()->get();
        $categoryUnits = $this->getCategoryUnits();
        return view('admin.inventories.edit', compact('inventory', 'categories', 'categoryUnits'));
    }

    public function update(Request $request, Inventory $inventory)
    {
        $data = $request->validate([
            'item_code' => ['required', 'string', 'max:255', Rule::unique('inventories', 'item_code')->ignore($inventory->id)->withoutTrashed()],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:inventory_categories,id',
            'quantity' => 'required|integer|min:0',
            'unit' => 'required|string',
            'unit_cost' => 'nullable|numeric|min:0',
            'status' => 'required|in:available,borrowed,maintenance,damaged',
            'condition' => 'required|in:new,good,fair,poor',
            'location' => 'nullable|string',
            'low_stock_threshold' => 'required|integer|min:0',
            'date_added' => 'required|date',
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

        $inventory->update($data);

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
        $inventories = Inventory::lowStock()->with(['category'])->get();
        return view('admin.inventories.low-stock', compact('inventories'));
    }

    public function generateQrCode(string $token)
    {
        abort_unless(preg_match('/\\AQR-[A-Z0-9]{32}\\z/', $token) === 1, 404);

        $inventory = Inventory::with('category:id,name')->where('qr_token', $token)->firstOrFail();
        abort_unless(function_exists('imagecreatetruecolor'), 500, 'The GD extension is required to generate QR downloads.');

        $matrix = Encoder::encode($inventory->qrUrl(), ErrorCorrectionLevel::H())->getMatrix();
        $size = 600;
        $quietZone = 4;
        $totalModules = $matrix->getWidth() + ($quietZone * 2);
        $moduleSize = intdiv($size, $totalModules);
        $offset = intdiv($size - ($totalModules * $moduleSize), 2) + ($quietZone * $moduleSize);
        $image = imagecreatetruecolor($size, $size);
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        imagefill($image, 0, 0, $white);

        for ($y = 0; $y < $matrix->getHeight(); $y++) {
            for ($x = 0; $x < $matrix->getWidth(); $x++) {
                if ($matrix->get($x, $y) === 1) {
                    imagefilledrectangle(
                        $image,
                        $offset + ($x * $moduleSize),
                        $offset + ($y * $moduleSize),
                        $offset + (($x + 1) * $moduleSize) - 1,
                        $offset + (($y + 1) * $moduleSize) - 1,
                        $black,
                    );
                }
            }
        }

        ob_start();
        imagejpeg($image, null, 95);
        $jpeg = ob_get_clean();
        imagedestroy($image);

        $filename = \Illuminate\Support\Str::slug(implode('-', array_filter([
            $inventory->item_code,
            $inventory->name,
            $inventory->category?->name,
        ]))) . '.jpg';

        return response($jpeg)->withHeaders([
            'Content-Type' => 'image/jpeg',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
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

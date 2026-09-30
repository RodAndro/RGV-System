<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class InventoryController extends Controller
{
    /**
     * Resolve a scanned QR payload or a manually entered item code to the
     * current inventory record and its live availability.
     */
    public function lookup(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'max:1000'],
        ]);

        $code = $this->extractItemCode($request->input('code'));

        $inventory = Inventory::with('category:id,name')
            ->where('item_code', $code)
            ->orWhere('id', $code)
            ->first();

        if (! $inventory) {
            throw ValidationException::withMessages([
                'code' => ['No inventory item matches this code.'],
            ]);
        }

        return response()->json(['item' => $this->itemPayload($inventory)]);
    }

    public function search(Request $request)
    {
        $q = trim((string) $request->input('q', ''));

        $inventories = Inventory::query()
            ->with('category:id,name')
            ->where('is_active', true)
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($inner) use ($q) {
                    $inner->where('name', 'like', "%{$q}%")
                        ->orWhere('item_code', 'like', "%{$q}%")
                        ->orWhere('brand', 'like', "%{$q}%");
                });
            })
            ->orderBy('name')
            ->limit(50)
            ->get();

        return response()->json([
            'items' => $inventories->map(fn ($inventory) => $this->itemPayload($inventory)),
        ]);
    }

    private function extractItemCode(string $raw): string
    {
        $raw = trim($raw);

        $decoded = json_decode($raw, true);

        if (is_array($decoded)) {
            foreach (['item_code', 'itemCode', 'code', 'id'] as $key) {
                if (isset($decoded[$key]) && $decoded[$key] !== '' && $decoded[$key] !== null) {
                    return (string) $decoded[$key];
                }
            }
        }

        return $raw;
    }

    private function itemPayload(Inventory $inventory): array
    {
        return [
            'id' => $inventory->id,
            'item_code' => $inventory->item_code,
            'name' => $inventory->name,
            'brand' => $inventory->brand,
            'description' => $inventory->description,
            'category' => $inventory->category?->name,
            'quantity' => (int) $inventory->quantity,
            'unit' => $inventory->unit,
            'status' => $inventory->status,
            'condition' => $inventory->condition,
            'image_path' => $inventory->image_path,
            'is_active' => (bool) $inventory->is_active,
        ];
    }
}

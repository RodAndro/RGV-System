<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\PaginatesResources;
use App\Http\Controllers\Controller;
use App\Models\InventoryCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventoryCategoryController extends Controller
{
    use PaginatesResources;

    public function index(Request $request): JsonResponse
    {
        $query = InventoryCategory::query();

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $this->applyListQuery($query, $request, ['name', 'slug'], ['id', 'name', 'created_at']);

        $paginator = $query->paginate($this->perPage($request, 50))->appends($request->query());

        return response()->json([
            'data' => $paginator->getCollection()->map(fn (InventoryCategory $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'icon' => $category->icon,
                'is_active' => (bool) $category->is_active,
            ])->all(),
            'meta' => [
                'page' => $paginator->currentPage(),
                'limit' => $paginator->perPage(),
                'total' => $paginator->total(),
                'total_pages' => $paginator->lastPage(),
            ],
        ]);
    }

    public function show(InventoryCategory $inventoryCategory): JsonResponse
    {
        return response()->json([
            'data' => [
                'id' => $inventoryCategory->id,
                'name' => $inventoryCategory->name,
                'slug' => $inventoryCategory->slug,
                'description' => $inventoryCategory->description,
                'icon' => $inventoryCategory->icon,
                'is_active' => (bool) $inventoryCategory->is_active,
                'inventory_count' => $inventoryCategory->inventories()->count(),
            ],
        ]);
    }
}

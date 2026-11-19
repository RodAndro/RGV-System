<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\PaginatesResources;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreInventoryRequest;
use App\Http\Requests\Api\V1\UpdateInventoryRequest;
use App\Http\Resources\InventoryResource;
use App\Models\Inventory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InventoryController extends Controller
{
    use PaginatesResources;

    private const SEARCHABLE = ['item_code', 'name', 'brand', 'description'];

    private const SORTABLE = ['id', 'item_code', 'name', 'quantity', 'status', 'created_at', 'updated_at'];

    public function index(Request $request): JsonResponse
    {
        $query = Inventory::query()->with('category:id,name');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->boolean('low_stock')) {
            $query->lowStock();
        }

        $this->applyListQuery($query, $request, self::SEARCHABLE, self::SORTABLE);

        $paginator = $query->paginate($this->perPage($request))->appends($request->query());

        return response()->json([
            'data' => InventoryResource::collection($paginator->getCollection())->resolve(),
            'meta' => [
                'page' => $paginator->currentPage(),
                'limit' => $paginator->perPage(),
                'total' => $paginator->total(),
                'total_pages' => $paginator->lastPage(),
            ],
        ]);
    }

    public function store(StoreInventoryRequest $request): JsonResponse
    {
        $inventory = Inventory::create($request->validated());
        $inventory->load('category:id,name');

        return (new InventoryResource($inventory))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED)
            ->header('Location', route('api.v1.inventories.show', $inventory));
    }

    public function show(Inventory $inventory): InventoryResource
    {
        $inventory->load('category:id,name');

        return new InventoryResource($inventory);
    }

    public function update(UpdateInventoryRequest $request, Inventory $inventory): InventoryResource
    {
        $inventory->update($request->validated());
        $inventory->load('category:id,name');

        return new InventoryResource($inventory);
    }

    public function destroy(Inventory $inventory): Response
    {
        $inventory->delete();

        return response()->noContent();
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $allowed = ['id', 'item_code', 'name', 'description', 'quantity', 'unit_cost', 'status', 'condition', 'category_id', 'created_at'];
        $fields = $this->fields($request, $allowed);

        $query = Inventory::query()
            ->select($fields)
            ->with('category:id,name')
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('category_id'), fn ($query) => $query->where('category_id', $request->category_id))
            ->orderBy('id');

        $etag = '"' . sha1(json_encode($request->query()) . ':' . Inventory::max('updated_at')) . '"';

        if ($request->headers->get('If-None-Match') === $etag) {
            return response('', 304)->header('ETag', $etag);
        }

        return response()->json($query->cursorPaginate((int) $request->input('per_page', 20)))
            ->header('ETag', $etag);
    }

    private function fields(Request $request, array $allowed): array
    {
        $fields = array_filter(array_map('trim', explode(',', (string) $request->input('fields', ''))));
        $selected = array_values(array_intersect($fields, $allowed));

        if (! in_array('id', $selected, true)) {
            $selected[] = 'id';
        }

        if (! in_array('category_id', $selected, true)) {
            $selected[] = 'category_id';
        }

        return $selected ?: $allowed;
    }
}

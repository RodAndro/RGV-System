<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInventoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $inventoryId = $this->route('inventory')?->id;

        return [
            'item_code' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('inventories', 'item_code')->ignore($inventoryId)->withoutTrashed()],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'brand' => ['sometimes', 'nullable', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'category_id' => ['sometimes', 'required', 'integer', 'exists:inventory_categories,id'],
            'quantity' => ['sometimes', 'required', 'integer', 'min:0'],
            'unit' => ['sometimes', 'required', 'string', 'max:50'],
            'unit_cost' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'status' => ['sometimes', 'required', Rule::in(['available', 'borrowed', 'maintenance', 'damaged'])],
            'condition' => ['sometimes', 'required', Rule::in(['new', 'good', 'fair', 'poor'])],
            'location' => ['sometimes', 'nullable', 'string', 'max:255'],
            'low_stock_threshold' => ['sometimes', 'required', 'integer', 'min:0'],
            'date_added' => ['sometimes', 'required', 'date'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}

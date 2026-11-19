<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInventoryRequest extends FormRequest
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
        return [
            'item_code' => ['required', 'string', 'max:255', Rule::unique('inventories', 'item_code')->withoutTrashed()],
            'name' => ['required', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category_id' => ['required', 'integer', 'exists:inventory_categories,id'],
            'quantity' => ['required', 'integer', 'min:0'],
            'unit' => ['required', 'string', 'max:50'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(['available', 'borrowed', 'maintenance', 'damaged'])],
            'condition' => ['required', Rule::in(['new', 'good', 'fair', 'poor'])],
            'location' => ['nullable', 'string', 'max:255'],
            'low_stock_threshold' => ['required', 'integer', 'min:0'],
            'date_added' => ['required', 'date'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'item_code' => $this->item_code,
            'name' => $this->name,
            'brand' => $this->brand,
            'description' => $this->description,
            'category_id' => $this->category_id,
            'category' => $this->whenLoaded('category', fn () => [
                'id' => $this->category?->id,
                'name' => $this->category?->name,
            ]),
            'quantity' => (int) $this->quantity,
            'unit' => $this->unit,
            'unit_cost' => $this->unit_cost,
            'status' => $this->status,
            'condition' => $this->condition,
            'location' => $this->location,
            'low_stock_threshold' => (int) $this->low_stock_threshold,
            'is_low_stock' => $this->quantity <= $this->low_stock_threshold,
            'is_active' => (bool) $this->is_active,
            'date_added' => $this->date_added?->toDateString(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

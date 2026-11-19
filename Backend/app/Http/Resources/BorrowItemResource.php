<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BorrowItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'inventory_id' => $this->inventory_id,
            'inventory' => $this->whenLoaded('inventory', fn () => [
                'id' => $this->inventory?->id,
                'item_code' => $this->inventory?->item_code,
                'name' => $this->inventory?->name,
                'unit' => $this->inventory?->unit,
            ]),
            'quantity' => (int) $this->quantity,
            'reserved_quantity' => (int) $this->reserved_quantity,
            'condition_borrowed' => $this->condition_borrowed,
            'condition_returned' => $this->condition_returned,
            'is_returned' => (bool) $this->is_returned,
            'returned_at' => $this->returned_at?->toIso8601String(),
            'damage_notes' => $this->damage_notes,
        ];
    }
}

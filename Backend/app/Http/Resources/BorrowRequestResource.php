<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BorrowRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'request_number' => $this->request_number,
            'status' => $this->status,
            'reason' => $this->reason,
            'employee_id' => $this->employee_id,
            'employee' => $this->whenLoaded('employee', fn () => [
                'id' => $this->employee?->id,
                'name' => $this->employee?->name,
                'email' => $this->employee?->email,
            ]),
            'approved_by' => $this->approved_by,
            'admin_remarks' => $this->admin_remarks,
            'borrow_date' => $this->borrow_date?->toDateString(),
            'due_date' => $this->due_date?->toDateString(),
            'return_date' => $this->return_date?->toDateString(),
            'return_location' => $this->return_location,
            'approved_at' => $this->approved_at?->toIso8601String(),
            'borrowed_at' => $this->borrowed_at?->toIso8601String(),
            'returned_at' => $this->returned_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'items' => BorrowItemResource::collection($this->whenLoaded('borrowItems')),
        ];
    }
}

<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreBorrowRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'max:1000'],
            'borrow_date' => ['required', 'date', 'after_or_equal:today'],
            'due_date' => ['required', 'date', 'after_or_equal:borrow_date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.inventory_id' => ['required', 'integer', 'exists:inventories,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }
}

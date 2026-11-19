<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBorrowRequestStatus extends FormRequest
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
            'status' => ['required', Rule::in(['approved', 'rejected', 'borrowed', 'returned'])],
            'remarks' => ['nullable', 'string', 'max:1000'],
            'return_location' => ['nullable', 'string', 'max:255'],
            'items' => ['sometimes', 'array'],
            'items.*.borrow_item_id' => ['required_with:items', 'integer'],
            'items.*.condition_returned' => ['required_with:items', Rule::in(['new', 'good', 'fair', 'poor', 'damaged'])],
            'items.*.damage_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}

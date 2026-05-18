<?php

namespace App\Imports;

use App\Models\ImportLog;
use App\Models\Inventory;
use App\Models\InventoryCategory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class InventoryImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    public function __construct(
        private readonly int $importLogId,
        private readonly string $duplicateStrategy = 'skip'
    ) {
    }

    public function collection(Collection $rows): void
    {
        $log = ImportLog::findOrFail($this->importLogId);
        $errors = $log->errors ?? [];
        $log->increment('total_rows', $rows->count());

        foreach ($rows as $index => $row) {
            $data = $this->normalizeRow($row->toArray());
            $validator = Validator::make($data, [
                'item_code' => ['required', 'string', 'max:255'],
                'name' => ['required', 'string', 'max:255'],
                'description' => ['required', 'string'],
                'category' => ['required', 'string', 'max:255'],
                'quantity' => ['required', 'integer', 'min:0'],
                'unit_cost' => ['required', 'numeric', 'min:0'],
                'unit' => ['nullable', 'string', 'max:50'],
                'status' => ['nullable', 'in:available,borrowed,maintenance,damaged'],
                'condition' => ['nullable', 'in:new,good,fair,poor'],
                'low_stock_threshold' => ['nullable', 'integer', 'min:0'],
            ]);

            if ($validator->fails()) {
                $errors[] = [
                    'row' => $log->processed_rows + $index + 2,
                    'item_code' => $data['item_code'] ?? null,
                    'errors' => $validator->errors()->all(),
                ];
                $log->increment('failed_rows');
                $log->increment('processed_rows');
                continue;
            }

            $category = InventoryCategory::firstOrCreate(
                ['slug' => Str::slug($data['category'])],
                ['name' => $data['category'], 'is_active' => true]
            );

            $payload = [
                'item_code' => $data['item_code'],
                'name' => $data['name'],
                'description' => $data['description'],
                'category_id' => $category->id,
                'quantity' => (int) $data['quantity'],
                'unit' => $data['unit'] ?: 'pcs',
                'unit_cost' => (float) $data['unit_cost'],
                'status' => $data['status'] ?: 'available',
                'condition' => $data['condition'] ?: 'good',
                'low_stock_threshold' => (int) ($data['low_stock_threshold'] ?: 5),
                'date_added' => $data['date_added'] ?: now()->toDateString(),
                'is_active' => true,
            ];

            $existing = Inventory::where('item_code', $payload['item_code'])->first();

            if ($existing && $this->duplicateStrategy === 'skip') {
                $errors[] = [
                    'row' => $log->processed_rows + $index + 2,
                    'item_code' => $payload['item_code'],
                    'errors' => ['Duplicate item skipped.'],
                ];
                $log->increment('failed_rows');
            } elseif ($existing) {
                $existing->update($payload);
                $log->increment('successful_rows');
            } else {
                Inventory::create($payload);
                $log->increment('successful_rows');
            }

            $log->increment('processed_rows');
        }

        $log->update(['errors' => $errors]);
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    private function normalizeRow(array $row): array
    {
        return [
            'item_code' => $row['item_code'] ?? $row['isbn'] ?? null,
            'name' => $row['name'] ?? $row['title'] ?? null,
            'description' => $row['description'] ?? null,
            'category' => $row['category'] ?? null,
            'quantity' => $row['quantity'] ?? $row['stock'] ?? null,
            'unit_cost' => $row['unit_cost'] ?? $row['price'] ?? null,
            'unit' => $row['unit'] ?? 'pcs',
            'status' => $row['status'] ?? 'available',
            'condition' => $row['condition'] ?? 'good',
            'low_stock_threshold' => $row['low_stock_threshold'] ?? 5,
            'date_added' => $row['date_added'] ?? now()->toDateString(),
        ];
    }
}

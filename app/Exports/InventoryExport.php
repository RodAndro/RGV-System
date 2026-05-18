<?php

namespace App\Exports;

use App\Models\Inventory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class InventoryExport implements FromQuery, WithHeadings, WithMapping, WithChunkReading
{
    public function __construct(
        private readonly array $filters = [],
        private readonly array $columns = []
    ) {
    }

    public function query()
    {
        return self::buildQuery($this->filters);
    }

    public function headings(): array
    {
        return array_map(fn ($column) => Str::headline($column), $this->selectedColumns());
    }

    public function map($inventory): array
    {
        return collect($this->selectedColumns())->map(function ($column) use ($inventory) {
            return match ($column) {
                'category' => $inventory->category?->name,
                'supplier' => $inventory->supplier?->name,
                default => $inventory->{$column},
            };
        })->all();
    }

    public static function buildQuery(array $filters = []): Builder
    {
        return Inventory::query()
            ->with(['category', 'supplier'])
            ->when($filters['category_id'] ?? null, fn ($query, $value) => $query->where('category_id', $value))
            ->when($filters['status'] ?? null, fn ($query, $value) => $query->where('status', $value))
            ->when($filters['stock_status'] ?? null, function ($query, $value) {
                if ($value === 'low') {
                    $query->whereColumn('quantity', '<=', 'low_stock_threshold');
                } elseif ($value === 'in_stock') {
                    $query->where('quantity', '>', 0);
                } elseif ($value === 'out') {
                    $query->where('quantity', 0);
                }
            })
            ->when($filters['min_price'] ?? null, fn ($query, $value) => $query->where('unit_cost', '>=', $value))
            ->when($filters['max_price'] ?? null, fn ($query, $value) => $query->where('unit_cost', '<=', $value))
            ->when($filters['date_from'] ?? null, fn ($query, $value) => $query->whereDate('created_at', '>=', $value))
            ->when($filters['date_to'] ?? null, fn ($query, $value) => $query->whereDate('created_at', '<=', $value))
            ->latest();
    }

    public function selectedColumns(): array
    {
        $allowed = ['item_code', 'name', 'description', 'category', 'quantity', 'unit', 'unit_cost', 'status', 'condition', 'supplier', 'date_added'];
        $selected = array_values(array_intersect($this->columns, $allowed));

        return $selected ?: ['item_code', 'name', 'category', 'quantity', 'unit_cost', 'status', 'condition'];
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}

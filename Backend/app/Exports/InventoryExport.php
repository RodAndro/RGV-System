<?php

namespace App\Exports;

use App\Models\Inventory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InventoryExport implements FromQuery, WithHeadings, WithMapping, WithChunkReading, WithStyles, ShouldAutoSize
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
        $columnLabels = [
            'item_code' => 'Item Code',
            'name' => 'Name',
            'description' => 'Description',
            'category' => 'Category',
            'quantity' => 'Quantity',
            'unit' => 'Unit',
            'unit_cost' => 'Unit Cost',
            'status' => 'Status',
            'condition' => 'Condition',
            'date_added' => 'Date Added',
        ];

        return array_map(fn ($col) => $columnLabels[$col] ?? Str::headline($col), $this->selectedColumns());
    }

    public function map($inventory): array
    {
        return collect($this->selectedColumns())->map(function ($column) use ($inventory) {
            return match ($column) {
                'status' => $inventory->trashed() ? 'Removed' : ucfirst($inventory->status),
                'condition' => ucfirst((string) $inventory->condition),
                'category' => $inventory->category?->name ?? 'N/A',
                default => $inventory->{$column},
            };
        })->all();
    }

    public static function buildQuery(array $filters = []): Builder
    {
        return Inventory::withTrashed()
            ->with(['category'])
            ->when($filters['category_id'] ?? null, fn ($query, $value) => $query->where('category_id', $value))
            ->when($filters['status'] ?? null, function ($query, $value) {
                if ($value === 'removed') {
                    $query->onlyTrashed();
                } else {
                    $query->where('status', $value);
                }
            })
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
        $allowed = ['item_code', 'name', 'description', 'category', 'quantity', 'unit', 'unit_cost', 'status', 'condition', 'date_added'];
        $selected = array_values(array_intersect($this->columns, $allowed));

        return $selected ?: ['item_code', 'name', 'category', 'quantity', 'unit_cost', 'status', 'condition'];
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function styles(Worksheet $sheet): array
    {
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        // 1. Table Header (Row 1): Matching PDF table header #1e40af
        $sheet->getStyle("A1:{$highestColumn}1")->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
                'name' => 'Arial',
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E40AF'],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(26);

        // Find the 'Status' column letter
        $statusCol = null;
        $headings = $this->headings();
        foreach ($headings as $index => $heading) {
            if (strcasecmp($heading, 'Status') === 0) {
                $statusCol = Coordinate::stringFromColumnIndex($index + 1);
                break;
            }
        }

        // 2. Data Rows: Zebra striping, borders, row heights, and status badges
        for ($row = 2; $row <= $highestRow; $row++) {
            $sheet->getRowDimension($row)->setRowHeight(22);

            // Base font and cell borders
            $sheet->getStyle("A{$row}:{$highestColumn}{$row}")->applyFromArray([
                'font' => [
                    'size' => 10,
                    'name' => 'Arial',
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'E5E7EB'],
                    ],
                ],
            ]);

            // Zebra striping
            if ($row % 2 === 0) {
                $sheet->getStyle("A{$row}:{$highestColumn}{$row}")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F9FAFB'],
                    ],
                ]);
            }

            // 3. Status column badge styling
            if ($statusCol) {
                $statusValue = strtolower(trim((string) $sheet->getCell("{$statusCol}{$row}")->getValue()));

                $badgeColors = match ($statusValue) {
                    'available' => ['bg' => 'DCFCE7', 'text' => '166534', 'border' => 'BBF7D0'],
                    'borrowed' => ['bg' => 'FEF3C7', 'text' => '92400E', 'border' => 'FDE68A'],
                    'maintenance' => ['bg' => 'FED7AA', 'text' => '9A3412', 'border' => 'FDBA74'],
                    'damaged' => ['bg' => 'FEE2E2', 'text' => '991B1B', 'border' => 'FECACA'],
                    'removed' => ['bg' => 'FEE2E2', 'text' => '991B1B', 'border' => 'FECACA'],
                    default => null,
                };

                if ($badgeColors) {
                    $sheet->getStyle("{$statusCol}{$row}")->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'color' => ['rgb' => $badgeColors['text']],
                            'size' => 10,
                            'name' => 'Arial',
                        ],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => $badgeColors['bg']],
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => $badgeColors['border']],
                            ],
                        ],
                    ]);
                }
            }
        }

        // Freeze top header row
        $sheet->freezePane('A2');

        return [];
    }
}

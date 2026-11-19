<?php

namespace App\Exports;

use App\Models\Booking;
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

class BookingsExport implements FromQuery, WithHeadings, WithMapping, WithChunkReading, WithStyles, ShouldAutoSize
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
            'reference_number' => 'Reference',
            'full_name' => 'Customer',
            'email' => 'Email',
            'contact_number' => 'Contact',
            'preferred_date' => 'Date',
            'preferred_time' => 'Time',
            'purpose_category' => 'Category',
            'status' => 'Status',
            'employee' => 'Assigned',
            'remarks' => 'Remarks',
        ];

        return array_map(fn ($col) => $columnLabels[$col] ?? Str::headline($col), $this->selectedColumns());
    }

    public function map($booking): array
    {
        return collect($this->selectedColumns())->map(function ($column) use ($booking) {
            return match ($column) {
                'status' => $booking->trashed() ? 'Removed' : ucfirst($booking->status),
                'employee' => $booking->employee ? $booking->employee->name : 'Unassigned',
                'preferred_date' => $booking->preferred_date ? $booking->preferred_date->format('M d, Y') : 'N/A',
                'preferred_time' => $booking->preferred_time_formatted,
                'purpose_category' => ucfirst(str_replace('-', ' ', (string) $booking->purpose_category)),
                default => $booking->{$column},
            };
        })->all();
    }

    public static function buildQuery(array $filters = []): Builder
    {
        return Booking::withTrashed()
            ->with('employee')
            ->when($filters['status'] ?? null, function ($query, $value) {
                if ($value === 'removed') {
                    $query->onlyTrashed();
                } else {
                    $query->where('status', $value);
                }
            })
            ->when($filters['customer'] ?? null, fn ($query, $value) => $query->where('full_name', 'like', "%{$value}%"))
            ->when($filters['date_from'] ?? null, fn ($query, $value) => $query->whereDate('preferred_date', '>=', $value))
            ->when($filters['date_to'] ?? null, fn ($query, $value) => $query->whereDate('preferred_date', '<=', $value))
            ->latest();
    }

    public function selectedColumns(): array
    {
        $allowed = ['reference_number', 'full_name', 'email', 'contact_number', 'preferred_date', 'preferred_time', 'purpose_category', 'status', 'employee', 'remarks'];
        $selected = array_values(array_intersect($this->columns, $allowed));

        return $selected ?: ['reference_number', 'full_name', 'email', 'contact_number', 'preferred_date', 'preferred_time', 'purpose_category', 'status', 'employee'];
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

            // Zebra striping (matching PDF: tr:nth-child(even) { background-color: #f9fafb; })
            if ($row % 2 === 0) {
                $sheet->getStyle("A{$row}:{$highestColumn}{$row}")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F9FAFB'],
                    ],
                ]);
            }

            // 3. Status column badge styling (matching PDF badges)
            if ($statusCol) {
                $statusValue = strtolower(trim((string) $sheet->getCell("{$statusCol}{$row}")->getValue()));

                $badgeColors = match ($statusValue) {
                    'approved' => ['bg' => 'DCFCE7', 'text' => '166534', 'border' => 'BBF7D0'],
                    'pending' => ['bg' => 'FEF3C7', 'text' => '92400E', 'border' => 'FDE68A'],
                    'completed' => ['bg' => 'DBEAFE', 'text' => '1E40AF', 'border' => 'BFDBFE'],
                    'rejected' => ['bg' => 'FEE2E2', 'text' => '991B1B', 'border' => 'FECACA'],
                    'removed' => ['bg' => 'FEE2E2', 'text' => '991B1B', 'border' => 'FECACA'],
                    'cancelled' => ['bg' => 'E5E7EB', 'text' => '374151', 'border' => 'D1D5DB'],
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

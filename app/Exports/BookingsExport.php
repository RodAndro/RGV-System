<?php

namespace App\Exports;

use App\Models\Booking;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class BookingsExport implements FromQuery, WithHeadings, WithMapping, WithChunkReading
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

    public function map($booking): array
    {
        return collect($this->selectedColumns())->map(function ($column) use ($booking) {
            return match ($column) {
                'employee' => $booking->employee?->name,
                default => $booking->{$column},
            };
        })->all();
    }

    public static function buildQuery(array $filters = []): Builder
    {
        return Booking::query()
            ->with('employee')
            ->when($filters['status'] ?? null, fn ($query, $value) => $query->where('status', $value))
            ->when($filters['customer'] ?? null, fn ($query, $value) => $query->where('full_name', 'like', "%{$value}%"))
            ->when($filters['date_from'] ?? null, fn ($query, $value) => $query->whereDate('preferred_date', '>=', $value))
            ->when($filters['date_to'] ?? null, fn ($query, $value) => $query->whereDate('preferred_date', '<=', $value))
            ->latest();
    }

    public function selectedColumns(): array
    {
        $allowed = ['reference_number', 'full_name', 'email', 'contact_number', 'preferred_date', 'preferred_time', 'purpose_category', 'status', 'employee', 'remarks'];
        $selected = array_values(array_intersect($this->columns, $allowed));

        return $selected ?: ['reference_number', 'full_name', 'email', 'preferred_date', 'preferred_time', 'status'];
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}

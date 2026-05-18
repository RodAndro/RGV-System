<?php

namespace App\Jobs;

use App\Exports\BookingsExport;
use App\Exports\InventoryExport;
use App\Exports\UsersExport;
use App\Models\ExportLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class GenerateExport implements ShouldQueue
{
    use Dispatchable, Queueable;

    public int $timeout = 900;

    public function __construct(private readonly int $exportLogId)
    {
    }

    public function handle(): void
    {
        $log = ExportLog::findOrFail($this->exportLogId);
        $log->update(['status' => 'processing', 'started_at' => now()]);

        try {
            $filePath = 'exports/' . $log->type . '-' . $log->id . '-' . now()->format('YmdHis') . '.' . $log->format;
            $export = $this->makeExport($log);

            if ($log->format === 'pdf') {
                $items = $export::buildQuery($log->filters ?? [])->limit(50000)->get();
                $view = $log->type === 'bookings' ? 'pdf.bookings' : 'pdf.inventory';
                $variable = $log->type === 'bookings' ? 'bookings' : 'inventories';
                Storage::disk('local')->put($filePath, Pdf::loadView($view, [$variable => $items])->output());
            } else {
                Excel::store($export, $filePath, 'local');
            }

            $log->update([
                'status' => 'completed',
                'file_path' => $filePath,
                'completed_at' => now(),
            ]);
        } catch (\Throwable $exception) {
            $log->update([
                'status' => 'failed',
                'failure_message' => $exception->getMessage(),
                'completed_at' => now(),
            ]);

            throw $exception;
        }
    }

    private function makeExport(ExportLog $log): InventoryExport|BookingsExport|UsersExport
    {
        return match ($log->type) {
            'bookings' => new BookingsExport($log->filters ?? [], $log->columns ?? []),
            'users' => new UsersExport(true),
            default => new InventoryExport($log->filters ?? [], $log->columns ?? []),
        };
    }
}

<?php

namespace App\Jobs;

use App\Imports\UsersImport;
use App\Models\ImportLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ProcessUsersImport implements ShouldQueue
{
    use Dispatchable, Queueable;

    public int $timeout = 600;

    public function __construct(private readonly int $importLogId, private readonly string $path)
    {
    }

    public function handle(): void
    {
        $log = ImportLog::findOrFail($this->importLogId);
        $log->update(['status' => 'processing', 'started_at' => now()]);

        try {
            Excel::import(new UsersImport($log->id), $this->path);
            $log->refresh();

            if ($log->failed_rows > 0) {
                $reportPath = 'imports/errors/users-' . $log->id . '.json';
                Storage::disk('local')->put($reportPath, json_encode($log->errors, JSON_PRETTY_PRINT));
                $log->error_report_path = $reportPath;
            }

            $log->update(['status' => 'completed', 'completed_at' => now()]);
        } catch (\Throwable $exception) {
            $log->update([
                'status' => 'failed',
                'errors' => array_merge($log->errors ?? [], [['errors' => [$exception->getMessage()]]]),
                'completed_at' => now(),
            ]);

            throw $exception;
        }
    }
}

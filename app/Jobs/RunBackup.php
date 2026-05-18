<?php

namespace App\Jobs;

use App\Mail\BackupCompleted;
use App\Mail\BackupFailed;
use App\Models\BackupMonitoring;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class RunBackup implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;

    public function __construct(
        public int $monitorId,
    ) {}

    public function handle(): void
    {
        $monitor = BackupMonitoring::find($this->monitorId);
        if (!$monitor) return;

        $monitor->update(['status' => 'processing']);

        try {
            Artisan::call('backup:run', ['--disable-notifications' => true]);

            $this->populateBackupFileInfo($monitor);

            $monitor->update([
                'status' => 'success',
                'message' => trim(Artisan::output()),
                'completed_at' => now(),
            ]);

            if ($email = env('BACKUP_NOTIFICATION_EMAIL')) {
                Mail::to($email)->send(new BackupCompleted($monitor->fresh()));
            }
        } catch (\Throwable $e) {
            $monitor->update([
                'status' => 'failed',
                'message' => $e->getMessage(),
                'completed_at' => now(),
            ]);

            if ($email = env('BACKUP_NOTIFICATION_EMAIL')) {
                Mail::to($email)->send(new BackupFailed($monitor->fresh()));
            }
        }
    }

    private function populateBackupFileInfo(BackupMonitoring $monitor): void
    {
        try {
            $disk = Storage::disk('local');
            $backupDir = config('backup.backup.name', 'Laravel');

            $files = collect($disk->files($backupDir))
                ->filter(fn ($f) => str_ends_with($f, '.zip'))
                ->sortByDesc(fn ($f) => $disk->lastModified($f));

            $latest = $files->first();

            if ($latest) {
                $fullPath = $disk->path($latest);
                $monitor->update([
                    'file_path' => $latest,
                    'size_bytes' => filesize($fullPath),
                    'checksum' => hash_file('sha256', $fullPath),
                ]);
            }
        } catch (\Throwable) {
            // Non-critical — file info is best-effort
        }
    }
}

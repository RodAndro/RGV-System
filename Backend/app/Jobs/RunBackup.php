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
use RuntimeException;
use ZipArchive;

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
        if (! $monitor) {
            return;
        }

        $monitor->update(['status' => 'processing']);

        try {
            $disk = Storage::disk('local');
            $backupDir = config('backup.backup.name', 'Laravel');
            $existingFiles = $disk->files($backupDir);
            $exitCode = Artisan::call('backup:run', ['--disable-notifications' => true]);
            $output = trim(Artisan::output());

            if ($exitCode !== 0) {
                throw new RuntimeException($output ?: "Backup command failed with exit code {$exitCode}.");
            }

            $fileInfo = $this->findNewBackupFile($existingFiles);

            $monitor->update([
                'status' => 'success',
                'file_path' => $fileInfo['path'],
                'size_bytes' => $fileInfo['size'],
                'checksum' => $fileInfo['checksum'],
                'message' => $output,
                'completed_at' => now(),
            ]);

            if ($email = env('BACKUP_NOTIFICATION_EMAIL')) {
                Mail::to($email)->send(new BackupCompleted($monitor->fresh()));
            }
        } catch (\Throwable $e) {
            report($e);
            $message = $e->getMessage();

            if (stripos($message, 'mysqldump') !== false
                && (stripos($message, 'not recognized') !== false || stripos($message, 'not found') !== false)) {
                $message = 'MySQL client tools missing: install mysqldump and set DB_DUMP_BINARY_PATH to its bin folder, or add it to PATH.';
            }

            $monitor->update([
                'status' => 'failed',
                'message' => $message,
                'completed_at' => now(),
            ]);

            if ($email = env('BACKUP_NOTIFICATION_EMAIL')) {
                Mail::to($email)->send(new BackupFailed($monitor->fresh()));
            }
        }
    }

    private function findNewBackupFile(array $existingFiles): array
    {
        $disk = Storage::disk('local');
        $backupDir = config('backup.backup.name', 'Laravel');
        $newFile = collect($disk->files($backupDir))
            ->filter(fn ($file) => str_ends_with(strtolower($file), '.zip'))
            ->reject(fn ($file) => in_array($file, $existingFiles, true))
            ->sortByDesc(fn ($file) => $disk->lastModified($file))
            ->first();

        if (! $newFile) {
            throw new RuntimeException('Backup command completed without creating a new ZIP file.');
        }

        $fullPath = $disk->path($newFile);
        $zip = new ZipArchive;

        if ($zip->open($fullPath) !== true) {
            throw new RuntimeException('Backup command created an unreadable ZIP file.');
        }

        $sqlFiles = 0;

        for ($index = 0; $index < $zip->numFiles; $index++) {
            $name = $zip->statIndex($index)['name'] ?? '';

            if (str_ends_with(strtolower($name), '.sql')) {
                $sqlFiles++;
            }
        }

        $zip->close();

        if ($sqlFiles !== 1) {
            throw new RuntimeException('Backup ZIP must contain exactly one SQL file.');
        }

        $size = filesize($fullPath);
        $checksum = hash_file('sha256', $fullPath);

        if ($size === false || $checksum === false) {
            throw new RuntimeException('Backup ZIP could not be measured or verified.');
        }

        return ['path' => $newFile, 'size' => $size, 'checksum' => $checksum];
    }
}

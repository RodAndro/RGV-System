<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\RunBackup;
use App\Models\BackupMonitoring;
use App\Services\DatabaseBackupRestoreService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Throwable;

class BackupController extends Controller
{
    public function index()
    {
        $retention = $this->retentionInfo();
        $backups = BackupMonitoring::latest()->paginate(20);

        foreach ($backups as $backup) {
            if (
                $backup->status === 'success'
                && (!$backup->file_path || !$backup->checksum || !Storage::disk('local')->exists($backup->file_path))
            ) {
                $backup->update(['status' => 'failed']);
            }
        }

        return view('admin.backups.index', [
            'backups' => $backups,
            'retention' => $retention,
        ]);
    }

    public function run()
    {
        $monitor = $this->createBackup();

        if ($monitor->status !== 'success') {
            return back()->with('error', $this->backupFailureMessage($monitor, 'Database backup failed. Check the backup configuration and try again.'));
        }

        return back()->with('success', 'Database backup created successfully.');
    }

    public function export()
    {
        $backup = $this->createBackup();

        if ($backup->status !== 'success') {
            return back()->with('error', $this->backupFailureMessage($backup, 'Database export failed. Check the backup configuration and try again.'));
        }

        return $this->download($backup);
    }

    public function import(Request $request, DatabaseBackupRestoreService $restoreService)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:zip', 'max:512000'],
        ]);

        try {
            $restoreService->restore($request->file('file'));
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors(['file' => $exception->getMessage()]);
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('error', 'Backup restore failed. The database transaction was rolled back.');
        }

        return back()->with('success', 'Database backup restored successfully.');
    }

    public function verify(BackupMonitoring $backup)
    {
        $result = $backup->verifyChecksum();

        if ($result === null) {
            return back()->with('error', 'Cannot verify — backup file is missing or no checksum recorded.');
        }

        if ($result) {
            return back()->with('success', 'Checksum verified — backup integrity confirmed.');
        }

        return back()->with('error', 'Checksum mismatch — backup file may be corrupted or tampered!');
    }

    public function download(BackupMonitoring $backup)
    {
        if (
            !$backup->file_path
            || !$backup->checksum
            || !Storage::disk('local')->exists($backup->file_path)
            || !$backup->verifyChecksum()
        ) {
            return back()->with('error', 'Backup file not found on disk.');
        }

        return Storage::disk('local')->download($backup->file_path, 'rgv-backup-' . $backup->created_at->format('Y-m-d-His') . '.zip');
    }

    private function createBackup(): BackupMonitoring
    {
        $monitor = BackupMonitoring::create([
            'disk' => 'local',
            'status' => 'queued',
            'started_at' => now(),
        ]);

        RunBackup::dispatchSync($monitor->id);

        return $monitor->fresh();
    }

    private function backupFailureMessage(BackupMonitoring $backup, string $fallback): string
    {
        return str_starts_with((string) $backup->message, 'PostgreSQL client tools missing:')
            ? $backup->message
            : $fallback;
    }

    public function clearAll()
    {
        BackupMonitoring::truncate();

        return back()->with('success', 'All backup records have been cleared.');
    }

    public function settings()
    {
        $schedule = $this->scheduleInfo();
        $notificationEmail = env('BACKUP_NOTIFICATION_EMAIL', '');
        $adminNotificationEmail = env('ADMIN_NOTIFICATION_EMAIL', '');
        $backupToS3 = env('BACKUP_TO_S3', false);

        return view('admin.backups.settings', compact('schedule', 'notificationEmail', 'adminNotificationEmail', 'backupToS3'));
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'notification_email' => ['nullable', 'email'],
            'admin_notification_email' => ['nullable', 'email'],
            'backup_to_s3' => ['boolean'],
        ]);

        $this->updateEnv('BACKUP_NOTIFICATION_EMAIL', $request->notification_email ?: '');
        $this->updateEnv('ADMIN_NOTIFICATION_EMAIL', $request->admin_notification_email ?: '');
        $this->updateEnv('BACKUP_TO_S3', $request->boolean('backup_to_s3') ? 'true' : 'false');

        return back()->with('success', 'Backup settings updated successfully.');
    }

    private function retentionInfo(): array
    {
        $strategy = config('backup.cleanup.default_strategy', []);

        return [
            'keep_all_backups_for_days' => $strategy['keep_all_backups_for_days'] ?? 1,
            'keep_daily_backups_for_days' => $strategy['keep_daily_backups_for_days'] ?? 7,
            'keep_weekly_backups_for_weeks' => $strategy['keep_weekly_backups_for_weeks'] ?? 4,
            'keep_monthly_backups_for_months' => $strategy['keep_monthly_backups_for_months'] ?? 12,
            'keep_yearly_backups_for_years' => $strategy['keep_yearly_backups_for_years'] ?? 2,
        ];
    }

    private function scheduleInfo(): array
    {
        return [
            ['task' => 'Full backup (files + database)', 'schedule' => 'Daily at 2:00 AM'],
            ['task' => 'Database-only backup', 'schedule' => 'Weekly on Sundays at 3:00 AM'],
            ['task' => 'Cleanup old backups', 'schedule' => 'Daily at 3:30 AM'],
        ];
    }

    private function updateEnv(string $key, string $value): void
    {
        $path = base_path('.env');

        if (!file_exists($path)) {
            return;
        }

        $content = file_get_contents($path);
        $pattern = '/^' . preg_quote($key, '/') . '=.*$/m';

        if (preg_match($pattern, $content)) {
            $content = preg_replace($pattern, $key . '=' . $value, $content);
        } else {
            $content .= PHP_EOL . $key . '=' . $value;
        }

        file_put_contents($path, $content);
    }
}

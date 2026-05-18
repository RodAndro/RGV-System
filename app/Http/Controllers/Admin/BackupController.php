<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\RunBackup;
use App\Models\BackupMonitoring;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BackupController extends Controller
{
    public function index()
    {
        $retention = $this->retentionInfo();

        return view('admin.backups.index', [
            'backups' => BackupMonitoring::latest()->paginate(20),
            'retention' => $retention,
        ]);
    }

    public function run()
    {
        $monitor = BackupMonitoring::create([
            'disk' => 'local',
            'status' => 'queued',
            'started_at' => now(),
        ]);

        RunBackup::dispatchSync($monitor->id);

        return back()->with('success', 'Backup queued. It will run in the background.');
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
        if (!$backup->file_path || !Storage::disk('local')->exists($backup->file_path)) {
            return back()->with('error', 'Backup file not found on disk.');
        }

        return Storage::disk('local')->download($backup->file_path, 'rgv-backup-' . $backup->created_at->format('Y-m-d-His') . '.zip');
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

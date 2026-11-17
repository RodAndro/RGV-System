<?php

namespace Tests\Feature;

use App\Jobs\RunBackup;
use App\Models\BackupMonitoring;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\WithRoles;
use Tests\TestCase;
use Spatie\Backup\Tasks\Backup\DbDumperFactory;
use ZipArchive;

class AdminDatabaseBackupTest extends TestCase
{
    use RefreshDatabase;
    use WithRoles;

    public function test_backup_archives_are_configured_for_postgresql_sql_only(): void
    {
        $this->assertSame(['pgsql'], config('backup.backup.source.databases'));
        $this->assertSame([], config('backup.backup.source.files.include'));
        $this->assertSame('sql', config('backup.backup.database_dump_file_extension'));
    }

    public function test_configured_postgresql_client_directory_is_used_for_dumps(): void
    {
        config()->set('database.connections.pgsql.dump.set_dump_binary_path', 'C:/Program Files/PostgreSQL/18/bin');

        $command = DbDumperFactory::createFromConnection('pgsql')->getDumpCommand('database.sql');

        $this->assertStringContainsString('C:/Program Files/PostgreSQL/18/bin/pg_dump', $command);
    }

    public function test_admin_can_open_backup_management_from_the_audit_logs_area(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)
            ->get(route('admin.backups.index'))
            ->assertOk()
            ->assertSee('Import Backup')
            ->assertSee(route('admin.backups.export'))
            ->assertSee(route('admin.backups.import'))
            ->assertSee('Audit Logs');
    }

    public function test_employee_cannot_access_backup_management(): void
    {
        $this->actingAs($this->createEmployee())
            ->get(route('admin.backups.index'))
            ->assertForbidden();
    }

    public function test_backup_history_respects_the_selected_page_size(): void
    {
        $admin = $this->createAdmin();

        foreach (range(1, 12) as $index) {
            BackupMonitoring::create([
                'disk' => 'local',
                'status' => 'failed',
                'message' => 'Demo backup ' . $index,
            ]);
        }

        $response = $this->actingAs($admin)
            ->get(route('admin.backups.index', ['per_page' => 10]))
            ->assertOk();
        $backups = $response->viewData('backups');

        $this->assertSame(10, $backups->perPage());
        $this->assertCount(10, $backups->items());
    }

    public function test_import_rejects_a_zip_without_a_sql_dump(): void
    {
        $admin = $this->createAdmin();
        $path = tempnam(sys_get_temp_dir(), 'rgv-backup-test-');
        $zip = new ZipArchive();
        $zip->open($path, ZipArchive::OVERWRITE);
        $zip->addFromString('readme.txt', 'No database dump');
        $zip->close();

        try {
            $upload = new UploadedFile($path, 'invalid-backup.zip', 'application/zip', null, true);

            $this->actingAs($admin)
                ->post(route('admin.backups.import'), ['file' => $upload])
                ->assertRedirect()
                ->assertSessionHasErrors('file');
        } finally {
            if (is_file($path)) {
                unlink($path);
            }
        }
    }

    public function test_failed_backup_command_is_not_marked_as_successful(): void
    {
        Mail::fake();
        $monitor = BackupMonitoring::create(['disk' => 'local', 'status' => 'queued']);
        Artisan::shouldReceive('call')->once()->andReturn(1);
        Artisan::shouldReceive('output')->once()->andReturn('Backup failed because pg_dump is unavailable.');

        RunBackup::dispatchSync($monitor->id);

        $this->assertDatabaseHas('backup_monitoring', [
            'id' => $monitor->id,
            'status' => 'failed',
            'message' => 'Backup failed because pg_dump is unavailable.',
            'file_path' => null,
        ]);
    }

    public function test_missing_windows_pg_dump_is_reported_with_setup_instructions(): void
    {
        Mail::fake();
        $monitor = BackupMonitoring::create(['disk' => 'local', 'status' => 'queued']);
        Artisan::shouldReceive('call')->once()->andReturn(1);
        Artisan::shouldReceive('output')->once()->andReturn('"pg_dump" is not recognized as an internal or external command.');

        RunBackup::dispatchSync($monitor->id);

        $this->assertDatabaseHas('backup_monitoring', [
            'id' => $monitor->id,
            'status' => 'failed',
            'message' => 'PostgreSQL client tools missing: install pg_dump and set DB_DUMP_BINARY_PATH to its bin folder, or add it to PATH.',
        ]);
    }

    public function test_successful_backup_records_a_verified_downloadable_zip(): void
    {
        Mail::fake();
        Storage::fake('local');
        $admin = $this->createAdmin();
        $monitor = BackupMonitoring::create(['disk' => 'local', 'status' => 'queued']);
        $backupPath = config('backup.backup.name') . '/database.zip';

        Artisan::shouldReceive('call')
            ->once()
            ->andReturnUsing(function () use ($backupPath): int {
                $disk = Storage::disk('local');
                $disk->makeDirectory(dirname($backupPath));
                $zip = new ZipArchive();
                $zip->open($disk->path($backupPath), ZipArchive::CREATE | ZipArchive::OVERWRITE);
                $zip->addFromString('db-dumps\\postgresql-postgres.sql', 'CREATE TABLE backup_test (id integer);');
                $zip->close();

                return 0;
            });
        Artisan::shouldReceive('output')->once()->andReturn('Backup completed.');

        RunBackup::dispatchSync($monitor->id);

        $monitor->refresh();

        $this->assertSame('success', $monitor->status);
        $this->assertSame($backupPath, $monitor->file_path);
        $this->assertGreaterThan(0, $monitor->size_bytes);
        $this->assertNotEmpty($monitor->checksum);
        $this->assertTrue($monitor->verifyChecksum());
        Storage::disk('local')->assertExists($backupPath);

        $this->actingAs($admin)
            ->get(route('admin.backups.download', $monitor))
            ->assertDownload('rgv-backup-' . $monitor->created_at->format('Y-m-d-His') . '.zip');
    }

    public function test_export_generates_and_downloads_a_new_backup_zip(): void
    {
        Mail::fake();
        Storage::fake('local');
        $admin = $this->createAdmin();
        $backupPath = config('backup.backup.name') . '/export.zip';

        Artisan::shouldReceive('call')
            ->once()
            ->andReturnUsing(function () use ($backupPath): int {
                $disk = Storage::disk('local');
                $disk->makeDirectory(dirname($backupPath));
                $zip = new ZipArchive();
                $zip->open($disk->path($backupPath), ZipArchive::CREATE | ZipArchive::OVERWRITE);
                $zip->addFromString('db-dumps\\postgresql-postgres.sql', 'CREATE TABLE backup_test (id integer);');
                $zip->close();

                return 0;
            });
        Artisan::shouldReceive('output')->once()->andReturn('Backup completed.');

        $this->actingAs($admin)
            ->post(route('admin.backups.export'))
            ->assertDownload();

        $this->assertDatabaseHas('backup_monitoring', [
            'status' => 'success',
            'file_path' => $backupPath,
        ]);
    }

    public function test_backup_page_downgrades_legacy_success_without_a_downloadable_file(): void
    {
        Storage::fake('local');
        $monitor = BackupMonitoring::create([
            'disk' => 'local',
            'status' => 'success',
            'message' => 'Backup failed because pg_dump is unavailable.',
        ]);

        $this->actingAs($this->createAdmin())
            ->get(route('admin.backups.index'))
            ->assertOk()
            ->assertSee('Failed');

        $this->assertDatabaseHas('backup_monitoring', [
            'id' => $monitor->id,
            'status' => 'failed',
        ]);
    }
}
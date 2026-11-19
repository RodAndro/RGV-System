<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use InvalidArgumentException;
use Symfony\Component\Process\Process;
use ZipArchive;

class DatabaseBackupRestoreService
{
    private const MAX_SQL_SIZE = 1073741824;

    public function restore(UploadedFile $archive): void
    {
        $zip = new ZipArchive;

        if ($zip->open($archive->getRealPath()) !== true) {
            throw new InvalidArgumentException('The uploaded file is not a valid ZIP archive.');
        }

        if ($password = config('backup.backup.password')) {
            $zip->setPassword($password);
        }

        $sqlPath = null;

        try {
            $sqlEntry = $this->findSqlEntry($zip);
            $sqlPath = $this->extractSqlEntry($zip, $sqlEntry['name'], $sqlEntry['size']);

            $connection = DB::connection();

            $driver = $connection->getDriverName();

            if (! in_array($driver, ['mysql', 'mariadb'], true)) {
                throw new InvalidArgumentException(
                    "Database restore is not supported for the '{$driver}' driver."
                );
            }

            $this->runMysql($connection->getConfig(), $sqlPath);
        } finally {
            $zip->close();

            if ($sqlPath && is_file($sqlPath)) {
                unlink($sqlPath);
            }
        }
    }

    private function findSqlEntry(ZipArchive $zip): array
    {
        $entries = [];

        for ($index = 0; $index < $zip->numFiles; $index++) {
            $entry = $zip->statIndex($index);
            $name = $entry['name'] ?? '';

            if (str_ends_with($name, '/') || ! str_ends_with(strtolower($name), '.sql')) {
                continue;
            }

            if (($entry['size'] ?? 0) > self::MAX_SQL_SIZE) {
                throw new InvalidArgumentException('The SQL file exceeds the 1 GB import limit.');
            }

            $entries[] = ['name' => $name, 'size' => (int) $entry['size']];
        }

        if (count($entries) !== 1) {
            throw new InvalidArgumentException('The ZIP archive must contain exactly one SQL file.');
        }

        return $entries[0];
    }

    private function extractSqlEntry(ZipArchive $zip, string $entryName, int $expectedSize): string
    {
        $directory = storage_path('app/backup-temp');
        File::ensureDirectoryExists($directory);
        $path = tempnam($directory, 'restore-');
        $input = $zip->getStream($entryName);
        $output = $path ? fopen($path, 'wb') : false;

        if (! $path || ! $input || ! $output) {
            if (is_resource($input)) {
                fclose($input);
            }

            if (is_resource($output)) {
                fclose($output);
            }

            if ($path && is_file($path)) {
                unlink($path);
            }

            throw new InvalidArgumentException('The SQL file in the ZIP archive could not be read.');
        }

        $copied = stream_copy_to_stream($input, $output);
        fclose($input);
        fclose($output);

        if ($copied !== $expectedSize) {
            unlink($path);
            throw new InvalidArgumentException('The SQL file in the ZIP archive is incomplete.');
        }

        return $path;
    }

    /**
     * Restore a mysqldump SQL file into the configured MySQL/MariaDB database.
     */
    private function runMysql(array $config, string $sqlPath): void
    {
        $binaryPath = trim((string) ($config['dump_binary_path'] ?? ''));
        $exe = PHP_OS_FAMILY === 'Windows' ? 'mysql.exe' : 'mysql';
        $client = $binaryPath === ''
            ? 'mysql'
            : rtrim($binaryPath, '/\\').DIRECTORY_SEPARATOR.$exe;

        $host = $config['host'] ?? null;
        $host = is_array($host) ? reset($host) : $host;

        $command = array_values(array_filter([
            $client,
            $host ? '--host='.$host : null,
            ! empty($config['port']) ? '--port='.$config['port'] : null,
            ! empty($config['username']) ? '--user='.$config['username'] : null,
            '--default-character-set='.($config['charset'] ?? 'utf8mb4'),
            '--database='.$config['database'],
        ]));

        // MYSQL_PWD avoids exposing the password in the process list.
        $environment = array_filter([
            'MYSQL_PWD' => $config['password'] ?? null,
        ], static fn ($value) => $value !== null && $value !== '');

        $handle = @fopen($sqlPath, 'rb');

        if ($handle === false) {
            throw new InvalidArgumentException('The extracted SQL file could not be opened.');
        }

        try {
            (new Process($command, null, $environment))
                ->setTimeout(3600)
                ->setInput($handle)
                ->mustRun();
        } finally {
            if (is_resource($handle)) {
                fclose($handle);
            }
        }
    }
}

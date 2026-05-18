<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\QueryPerformanceLog;
use DB;

class SystemMetricsService
{
    public static function collect(): array
    {
        $diskTotal = self::safeDiskSpace();
        $diskFree = self::safeDiskFreeSpace();

        return [
            'disk_total_gb' => $diskTotal !== null ? round($diskTotal / 1024 / 1024 / 1024, 2) : null,
            'disk_free_gb' => $diskFree !== null ? round($diskFree / 1024 / 1024 / 1024, 2) : null,
            'disk_usage_percent' => $diskTotal ? round(100 - ($diskFree / $diskTotal * 100), 1) : null,
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'memory_limit' => ini_get('memory_limit'),
            'queue_size' => DB::table('jobs')->count(),
            'failed_jobs' => DB::table('failed_jobs')->count(),
            'storage_warning' => $diskTotal !== null && $diskFree !== null && ($diskFree / $diskTotal) < 0.15,
            'failed_logins_today' => AuditLog::where('event', 'failed login')->whereDate('created_at', today())->count(),

            // System health
            'server_uptime_days' => self::getUptime(),
            'db_size_mb' => self::getDatabaseSize(),
            'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 1),
            'memory_peak_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 1),

            // Performance metrics
            'requests_today' => AuditLog::where('event', 'page_visit')->whereDate('created_at', today())->count(),
            'requests_last_minute' => AuditLog::where('event', 'page_visit')->where('created_at', '>=', now()->subMinute())->count(),
            'avg_response_time_ms' => self::getAvgResponseTime(),
            'slow_queries_today' => self::countModel(QueryPerformanceLog::class, today(), 'duration_ms', '>', 1000),
            'total_queries_today' => self::countModel(QueryPerformanceLog::class, today()),
        ];
    }

    private static function getUptime(): ?int
    {
        try {
            if (PHP_OS_FAMILY === 'Windows') {
                $output = @shell_exec('net statistics workstation 2>&1');
                if ($output && preg_match('/since\s+(.+)/i', $output, $matches)) {
                    $bootTime = strtotime($matches[1]);
                    if ($bootTime) {
                        return (int) floor((time() - $bootTime) / 86400);
                    }
                }
                return null;
            }

            if (@file_exists('/proc/uptime')) {
                $uptime = (float) @file_get_contents('/proc/uptime');
                return (int) floor($uptime / 86400);
            }

            return null;
        } catch (\Throwable) {
            return null;
        }
    }

    private static function getDatabaseSize(): ?float
    {
        try {
            $connection = config('database.default');
            $driver = config("database.connections.{$connection}.driver");

            if ($driver === 'sqlite') {
                $path = database_path(config("database.connections.{$connection}.database"));
                return file_exists($path) ? round(filesize($path) / 1024 / 1024, 1) : null;
            }

            if ($driver === 'mysql') {
                $dbName = config("database.connections.{$connection}.database");
                $result = DB::select(
                    "SELECT SUM(data_length + index_length) / 1024 / 1024 AS size_mb
                     FROM information_schema.tables
                     WHERE table_schema = ?", [$dbName]
                );
                return round($result[0]->size_mb ?? 0, 1);
            }

            return null;
        } catch (\Throwable) {
            return null;
        }
    }

    private static function getAvgResponseTime(): ?float
    {
        try {
            $times = AuditLog::where('event', 'page_visit')
                ->whereDate('created_at', today())
                ->whereNotNull('new_values')
                ->latest()
                ->limit(100)
                ->get()
                ->map(fn ($log) => $log->new_values['response_time_ms'] ?? null)
                ->filter();

            return $times->isNotEmpty() ? round($times->avg()) : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private static function countModel(string $modelClass, $date, string $column = 'created_at', string $operator = '>=', mixed $value = null): int
    {
        try {
            if (!class_exists($modelClass)) {
                return 0;
            }
            $query = $modelClass::whereDate($column, $date);
            if ($value !== null) {
                $query->where($column, $operator, $value);
            }
            return $query->count();
        } catch (\Throwable) {
            return 0;
        }
    }

    private static function safeDiskSpace(): ?float
    {
        $diskPath = base_path();
        try {
            return disk_total_space($diskPath) ?: null;
        } catch (\Throwable) {
            return null;
        }
    }

    private static function safeDiskFreeSpace(): ?float
    {
        $diskPath = base_path();
        try {
            return disk_free_space($diskPath) ?: null;
        } catch (\Throwable) {
            return null;
        }
    }
}

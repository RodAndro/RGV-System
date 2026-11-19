<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Small helpers for the few places where date functions differ between
 * database engines. Keeping them here avoids copy-pasted driver branches
 * drifting apart across controllers.
 */
final class SqlDialect
{
    public static function driver(): string
    {
        return DB::connection()->getDriverName();
    }

    /**
     * Build an expression that formats a date column.
     *
     * Supported formats: 'YYYY-MM' (year and month) and 'MM' (month only).
     * Returns a raw SQL string suitable for selectRaw()/select().
     */
    public static function monthExpression(string $column, string $format = 'YYYY-MM'): string
    {
        $driver = self::driver();

        return match ($driver) {
            'mysql', 'mariadb' => sprintf(
                "DATE_FORMAT(%s, '%s')",
                $column,
                $format === 'MM' ? '%m' : '%Y-%m',
            ),
            // SQLite (used by the test suite).
            default => sprintf(
                "strftime('%s', %s)",
                $format === 'MM' ? '%m' : '%Y-%m',
                $column,
            ),
        };
    }
}

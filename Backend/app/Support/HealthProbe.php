<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Small seam around the database probe used by GET /api/health.
 *
 * Extracting it keeps the route free of try/catch noise and lets tests
 * simulate an unavailable database without mocking the DB facade (which
 * corrupts transactional teardown).
 */
class HealthProbe
{
    public function databaseStatus(): string
    {
        try {
            DB::connection()->select('select 1');

            return 'ok';
        } catch (Throwable) {
            return 'unavailable';
        }
    }

    public function isHealthy(): bool
    {
        return $this->databaseStatus() === 'ok';
    }
}

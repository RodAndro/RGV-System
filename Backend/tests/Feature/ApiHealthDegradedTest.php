<?php

namespace Tests\Feature;

use App\Support\HealthProbe;
use Tests\TestCase;

class ApiHealthDegradedTest extends TestCase
{
    public function test_health_endpoint_reports_degraded_when_database_is_unavailable(): void
    {
        $this->app->instance(HealthProbe::class, new class extends HealthProbe
        {
            public function databaseStatus(): string
            {
                return 'unavailable';
            }
        });

        $this->getJson('/api/health')
            ->assertStatus(503)
            ->assertJsonPath('status', 'degraded')
            ->assertJsonPath('database', 'unavailable');
    }
}

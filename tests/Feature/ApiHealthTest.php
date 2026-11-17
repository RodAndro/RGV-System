<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiHealthTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_endpoint_reports_version_commit_and_database_status(): void
    {
        config(['app.version' => 'test-version']);
        putenv('APP_COMMIT=test-commit');

        $this->getJson('/api/health')
            ->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('version', 'test-version')
            ->assertJsonPath('commit', 'test-commit')
            ->assertJsonPath('database', 'ok');

        putenv('APP_COMMIT');
    }

    public function test_openapi_document_is_available_at_api_docs(): void
    {
        $this->get('/api/docs')
            ->assertOk()
            ->assertHeader('content-type', 'application/yaml; charset=utf-8')
            ->assertSee('openapi: 3.0.3', false);
    }
}

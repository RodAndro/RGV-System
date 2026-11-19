<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Spatie\Permission\PermissionRegistrar;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Roles and permissions are cached in a static property. With
        // RefreshDatabase each test rolls its rows back, so a cached role can
        // outlive the row it points at and the next test raises
        // RoleDoesNotExist. Clearing before use makes every test independent
        // of execution order.
        $this->forgetPermissionCache();
    }

    protected function tearDown(): void
    {
        // Also clear after, so this test cannot leak state into the next one.
        $this->forgetPermissionCache();

        parent::tearDown();
    }

    private function forgetPermissionCache(): void
    {
        try {
            if ($this->app !== null) {
                $this->app->make(PermissionRegistrar::class)->forgetCachedPermissions();
            }
        } catch (\Throwable) {
            // The container may not be booted yet; nothing to clear.
        }
    }
}

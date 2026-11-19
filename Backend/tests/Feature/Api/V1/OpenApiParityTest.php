<?php

namespace Tests\Feature\Api\V1;

use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\Route;
use Symfony\Component\Yaml\Yaml;
use Tests\TestCase;

/**
 * Requirement B7: every endpoint appears in the OpenAPI document.
 *
 * This guards against drift between routes and the published spec, which is
 * easy to break when an endpoint is added.
 */
class OpenApiParityTest extends TestCase
{
    public function test_every_api_endpoint_is_documented(): void
    {
        $specPath = base_path('../docs/api/openapi.yaml');

        $this->assertFileExists($specPath, 'The OpenAPI document is missing.');

        $spec = Yaml::parseFile($specPath);
        $this->assertIsArray($spec['paths'] ?? null, 'The spec has no paths.');

        // Normalise path parameters so {id} matches {inventory}.
        $normalise = static fn (string $path): string => preg_replace('/\{[^}]+\}/', '{}', $path);

        $documented = [];
        foreach (array_keys($spec['paths']) as $path) {
            $documented[$normalise($path)] = $path;
        }

        // Endpoints served by the team's own API surface.
        $served = [];
        foreach (Route::getRoutes() as $route) {
            /** @var RoutingRoute $route */
            $uri = $route->uri();

            if (! str_starts_with($uri, 'api/')) {
                continue;
            }

            // These two serve the specification itself.
            if (in_array($uri, ['api/docs', 'api/openapi.yaml'], true)) {
                continue;
            }

            $served[$normalise('/'.ltrim(substr($uri, 3), '/'))] = '/'.ltrim(substr($uri, 3), '/');
        }

        $missing = array_diff_key($served, $documented);

        $this->assertSame(
            [],
            array_values($missing),
            'These endpoints are served but absent from docs/api/openapi.yaml: '
                .implode(', ', $missing),
        );
    }

    public function test_the_spec_is_valid_and_versioned(): void
    {
        $spec = Yaml::parseFile(base_path('../docs/api/openapi.yaml'));

        $this->assertStringStartsWith('3.', $spec['openapi']);
        $this->assertSame('/api', $spec['servers'][0]['url']);
        $this->assertArrayHasKey('bearerAuth', $spec['components']['securitySchemes']);
        $this->assertArrayHasKey('Error', $spec['components']['schemas']);
    }
}

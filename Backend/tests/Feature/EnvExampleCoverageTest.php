<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Requirement Q5: `.env.example` lists every required variable with dummy
 * values. This test fails when the application reads an environment variable
 * that the example file does not document, which is how a deployment ends up
 * missing configuration.
 */
class EnvExampleCoverageTest extends TestCase
{
    public function test_every_variable_read_by_application_code_is_documented(): void
    {
        $examplePath = base_path('.env.example');
        $this->assertFileExists($examplePath);

        $example = (string) file_get_contents($examplePath);
        preg_match_all('/^([A-Z][A-Z0-9_]+)=/m', $example, $matches);
        $documented = array_flip($matches[1]);

        // Only the team's own code, not stock framework config.
        $files = array_merge(
            $this->phpFiles(base_path('app')),
            $this->phpFiles(base_path('routes')),
        );

        $used = [];
        foreach ($files as $file) {
            $code = (string) file_get_contents($file);
            if (preg_match_all("/env\\(\\s*'([A-Z][A-Z0-9_]+)'/", $code, $found)) {
                foreach ($found[1] as $key) {
                    $used[$key] = true;
                }
            }
        }

        $undocumented = array_values(array_diff(array_keys($used), array_keys($documented)));
        sort($undocumented);

        $this->assertSame(
            [],
            $undocumented,
            'These environment variables are read by application code but missing '
                .'from Backend/.env.example: '.implode(', ', $undocumented),
        );
    }

    public function test_the_example_contains_no_real_secrets(): void
    {
        $example = (string) file_get_contents(base_path('.env.example'));

        // Guard against a real credential being pasted into the example file.
        $this->assertDoesNotMatchRegularExpression('/sb_secret_[A-Za-z0-9_\-]{10,}/', $example);
        $this->assertDoesNotMatchRegularExpression('/sb_publishable_[A-Za-z0-9_\-]{10,}/', $example);
        $this->assertDoesNotMatchRegularExpression('/postgres\.[a-z0-9]{15,}/', $example);
    }

    /**
     * @return array<int, string>
     */
    private function phpFiles(string $directory): array
    {
        if (! is_dir($directory)) {
            return [];
        }

        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }
}

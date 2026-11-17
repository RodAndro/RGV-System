<?php

namespace Tests\Feature;

use App\Console\Commands\MigrateSqliteToSupabase;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SqliteToSupabaseMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_migration_scope_orders_categories_and_foreign_key_dependents(): void
    {
        $tables = MigrateSqliteToSupabase::TABLE_ORDER;

        $this->assertContains('inventory_categories', $tables);
        $this->assertContains('inventories', $tables);
        $this->assertContains('borrow_requests', $tables);
        $this->assertContains('borrow_items', $tables);
        $this->assertLessThan(array_search('inventories', $tables, true), array_search('inventory_categories', $tables, true));
        $this->assertLessThan(array_search('borrow_items', $tables, true), array_search('borrow_requests', $tables, true));
        $this->assertLessThan(array_search('borrow_items', $tables, true), array_search('inventories', $tables, true));
    }

    public function test_mapping_table_is_additive_and_supports_repeatable_source_keys(): void
    {
        $this->assertTrue(Schema::hasTable('data_migration_mappings'));
        $this->assertTrue(Schema::hasColumns('data_migration_mappings', [
            'source_fingerprint', 'source_table', 'source_key', 'target_key',
        ]));
    }

    public function test_source_key_mapping_cannot_be_written_twice(): void
    {
        $mapping = [
            'source_fingerprint' => str_repeat('a', 64),
            'source_table' => 'inventory_categories',
            'source_key' => '{"id":7}',
            'target_key' => '{"id":12}',
            'created_at' => now(),
            'updated_at' => now(),
        ];

        DB::table('data_migration_mappings')->insert($mapping);
        $this->assertDatabaseHas('data_migration_mappings', [
            'source_fingerprint' => $mapping['source_fingerprint'],
            'source_table' => 'inventory_categories',
            'source_key' => '{"id":7}',
            'target_key' => '{"id":12}',
        ]);

        $this->expectException(QueryException::class);
        DB::table('data_migration_mappings')->insert($mapping);
    }

    public function test_missing_source_is_rejected_before_database_connection(): void
    {
        $exitCode = Artisan::call('rvg:migrate-sqlite-to-supabase', [
            '--sqlite' => 'database/this-source-file-must-not-exist.sqlite',
        ]);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('Source SQLite file does not exist', Artisan::output());
    }
}

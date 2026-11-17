<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\BorrowItem;
use App\Models\BorrowRequest;
use App\Models\BorrowReturnEvidence;
use App\Models\Inventory;
use App\Models\InventoryCategory;
use App\Models\Report;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PDO;
use RuntimeException;
use Throwable;

class MigrateSqliteToSupabase extends Command
{
    protected $signature = 'rvg:migrate-sqlite-to-supabase
        {--sqlite=database/database.sqlite : Path to the source SQLite database}
        {--execute : Apply the merge into the configured PostgreSQL database (dry-run is the default)}
        {--backup-confirmed : Confirm that protected source and target backups exist}
        {--yes : Skip the interactive confirmation after preflight}';

    protected $description = 'Preflight, dry-run, or explicitly merge Laravel business and historical records into PostgreSQL.';

    /**
     * Parent tables precede their dependents. Runtime-only Laravel tables and
     * the migrations repository are intentionally excluded.
     */
    public const TABLE_ORDER = [
        'roles', 'permissions', 'users', 'inventory_categories', 'settings', 'suppliers',
        'bookings', 'inventories', 'borrow_requests', 'borrow_return_evidences', 'borrow_items',
        'notifications', 'notification_preferences', 'reports', 'activity_log', 'audit_logs',
        'login_history', 'import_logs', 'export_logs', 'scheduled_tasks', 'api_rate_limits',
        'backup_monitoring', 'model_has_permissions', 'model_has_roles', 'role_has_permissions',
        'personal_access_tokens', 'mobile_return_idempotency', 'chatbot_interactions',
    ];

    private const REQUIRED_TABLES = [
        'users', 'roles', 'permissions', 'inventory_categories', 'inventories',
        'bookings', 'borrow_requests', 'borrow_items', 'notifications',
    ];

    /** Stable unique keys used to merge matching pre-existing PostgreSQL rows. */
    private const NATURAL_KEYS = [
        'roles' => ['name', 'guard_name'], 'permissions' => ['name', 'guard_name'],
        'users' => ['email'], 'inventory_categories' => ['slug'], 'settings' => ['key'],
        'bookings' => ['reference_number'], 'inventories' => ['item_code'],
        'borrow_requests' => ['request_number'], 'borrow_return_evidences' => ['idempotency_key'],
        'notification_preferences' => ['user_id', 'type'], 'reports' => ['report_number'],
        'personal_access_tokens' => ['token'],
    ];

    /** Foreign keys and polymorphic user links requiring source-to-target IDs. */
    private const FOREIGN_KEYS = [
        'bookings' => ['employee_id' => 'users'],
        'inventories' => ['category_id' => 'inventory_categories', 'supplier_id' => 'suppliers'],
        'borrow_requests' => ['employee_id' => 'users', 'approved_by' => 'users'],
        'borrow_return_evidences' => ['borrow_request_id' => 'borrow_requests', 'uploaded_by' => 'users'],
        'borrow_items' => [
            'borrow_request_id' => 'borrow_requests', 'inventory_id' => 'inventories',
            'return_evidence_id' => 'borrow_return_evidences',
        ],
        'notifications' => ['user_id' => 'users'], 'notification_preferences' => ['user_id' => 'users'],
        'reports' => ['generated_by' => 'users'], 'audit_logs' => ['user_id' => 'users'],
        'login_history' => ['user_id' => 'users'], 'import_logs' => ['user_id' => 'users'],
        'export_logs' => ['user_id' => 'users'], 'api_rate_limits' => ['user_id' => 'users'],
        'model_has_permissions' => ['permission_id' => 'permissions'],
        'model_has_roles' => ['role_id' => 'roles'],
        'role_has_permissions' => ['permission_id' => 'permissions', 'role_id' => 'roles'],
        'mobile_return_idempotency' => ['employee_id' => 'users', 'borrow_request_id' => 'borrow_requests'],
        'chatbot_interactions' => ['user_id' => 'users'],
    ];

    public function handle(): int
    {
        $path = realpath(base_path($this->option('sqlite')));
        if (! $path || ! is_file($path)) {
            $this->error('Source SQLite file does not exist.');

            return self::FAILURE;
        }

        if (config('database.default') !== 'pgsql') {
            $this->error('The configured default connection must be pgsql. This command never changes connection settings.');

            return self::FAILURE;
        }

        if ($this->option('execute') && ! $this->option('backup-confirmed')) {
            $this->error('Execution requires --backup-confirmed after you have protected source and target backups.');

            return self::FAILURE;
        }

        $source = new PDO('sqlite:'.$path, options: [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
        $source->exec('PRAGMA foreign_keys = ON');
        $target = DB::connection('pgsql');
        $fingerprint = hash('sha256', $path);
        $phase = 'preflight';

        try {
            $tables = $this->preflight($source, $fingerprint);
            $this->showPlan($source, $tables);

            if (! $this->option('execute')) {
                $this->info('Dry-run only. No rows were written. Add --execute --backup-confirmed after reviewing the plan.');

                return self::SUCCESS;
            }

            if (! $this->option('yes') && ! $this->confirm('Merge source rows into this configured PostgreSQL database? Existing target rows will be preserved.')) {
                $this->warn('Aborted without writing rows.');

                return self::FAILURE;
            }

            $counts = [];
            foreach ($tables as $table) {
                $phase = $table;
                $counts[$table] = $this->migrateTable($source, $table, $fingerprint);
            }

            $phase = 'final reconciliation';
            $this->reconcile($source, $tables, $fingerprint, $counts);
            $this->info('Migration merge and source-to-target key reconciliation completed.');

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $safeDetail = $exception instanceof RuntimeException ? ' '.$exception->getMessage() : '';
            $this->error("Migration stopped during {$phase} with ".$exception::class.'.'.$safeDetail.' Database exception details and values are suppressed. Review the source/target schema, then retry.');

            return self::FAILURE;
        }
    }

    private function preflight(PDO $source, string $fingerprint): array
    {
        $foreignKeyErrors = $source->query('PRAGMA foreign_key_check')->fetchAll();
        if ($foreignKeyErrors !== []) {
            throw new RuntimeException('SQLite source contains foreign-key orphans; repair or explicitly resolve them before migration.');
        }
        $sourceTables = array_column($source->query("SELECT name FROM sqlite_master WHERE type = 'table'")->fetchAll(), 'name');
        $missingSourceTables = array_diff(self::REQUIRED_TABLES, $sourceTables);
        if ($missingSourceTables !== []) {
            throw new RuntimeException('SQLite source is missing required business tables: '.implode(', ', $missingSourceTables));
        }
        $tables = array_values(array_filter(self::TABLE_ORDER, fn ($table) => in_array($table, $sourceTables, true)));
        if ($tables === []) {
            throw new RuntimeException('No supported application tables were found in the SQLite source.');
        }

        foreach (['data_migration_mappings', ...$tables] as $table) {
            if (! Schema::connection('pgsql')->hasTable($table)) {
                throw new RuntimeException("PostgreSQL table {$table} is missing. Apply the reviewed Laravel migrations first.");
            }
        }

        foreach ($tables as $table) {
            $sourceColumns = $this->sourceColumns($source, $table);
            $targetColumns = Schema::connection('pgsql')->getColumnListing($table);
            $missing = array_diff($sourceColumns, $targetColumns);
            $missing = array_merge($missing, array_diff(self::NATURAL_KEYS[$table] ?? [], $sourceColumns));
            if ($missing !== []) {
                throw new RuntimeException("Schema mismatch for {$table}; target is missing source columns: ".implode(', ', $missing));
            }
            if ($this->primaryKey($source, $table) === []) {
                throw new RuntimeException("Source table {$table} has no primary key and cannot be safely reconciled.");
            }
        }

        $this->targetFingerprintCheck($fingerprint);

        return $tables;
    }

    private function targetFingerprintCheck(string $fingerprint): void
    {
        // Validate access and schema without emitting connection details.
        DB::connection('pgsql')->select('select 1');
        DB::connection('pgsql')->table('data_migration_mappings')->where('source_fingerprint', $fingerprint)->count();
    }

    private function showPlan(PDO $source, array $tables): void
    {
        $rows = [];
        foreach ($tables as $table) {
            $sourceCount = (int) $source->query('SELECT COUNT(*) FROM "'.$table.'"')->fetchColumn();
            $rows[] = [$table, $sourceCount];
        }
        $this->table(['Table', 'Source rows'], $rows);
    }

    private function migrateTable(PDO $source, string $table, string $fingerprint): int
    {
        $connection = DB::connection('pgsql');
        $primaryKey = $this->primaryKey($source, $table);
        $naturalKey = self::NATURAL_KEYS[$table] ?? [];
        $rows = $source->query('SELECT * FROM "'.$table.'"')->fetchAll();
        $mapped = 0;

        foreach ($rows as $sourceRow) {
            $connection->transaction(function () use ($connection, $sourceRow, $table, $fingerprint, $primaryKey, $naturalKey, &$mapped) {
                $sourceKey = $this->keyJson($sourceRow, $primaryKey);
                $existingMap = $connection->table('data_migration_mappings')
                    ->where('source_fingerprint', $fingerprint)->where('source_table', $table)->where('source_key', $sourceKey)->first();
                if ($existingMap) {
                    $targetKey = json_decode($existingMap->target_key, true, flags: JSON_THROW_ON_ERROR);
                    if (! $connection->table($table)->where($targetKey)->exists()) {
                        throw new RuntimeException("Stored target mapping for {$table} no longer points to an existing row.");
                    }
                    $mapped++;

                    return;
                }

                $row = $this->mapForeignKeys($sourceRow, $table, $fingerprint);
                $row = $this->mapPolymorphicKeys($row, $table, $fingerprint);
                if ($table === 'mobile_return_idempotency') {
                    $payload = json_decode($row['response_payload'], true, flags: JSON_THROW_ON_ERROR);
                    $row['response_payload'] = json_encode($this->mapReturnResponseIds($payload, $fingerprint), JSON_THROW_ON_ERROR);
                }
                $existing = null;
                if ($naturalKey !== [] && ! in_array(null, array_intersect_key($row, array_flip($naturalKey)), true)) {
                    $query = $connection->table($table);
                    foreach ($naturalKey as $column) {
                        $query->where($column, $row[$column]);
                    }
                    $existing = $query->first();
                }

                if ($existing) {
                    $targetKey = array_combine($primaryKey, array_map(fn ($key) => $existing->{$key}, $primaryKey));
                } else {
                    $targetKey = $this->insertRow($table, $row, $primaryKey, $sourceRow, $naturalKey !== []);
                }

                $connection->table('data_migration_mappings')->insert([
                    'source_fingerprint' => $fingerprint, 'source_table' => $table,
                    'source_key' => $sourceKey, 'target_key' => $this->keyJson($targetKey, $primaryKey),
                    'created_at' => now(), 'updated_at' => now(),
                ]);
                $mapped++;
            });
        }

        $this->info("{$table}: {$mapped}/".count($rows).' source rows mapped.');
        if (in_array('id', $primaryKey, true)) {
            $sequence = $table.'_id_seq';
            DB::connection('pgsql')->statement("SELECT setval(pg_get_serial_sequence('public.{$table}', 'id'), GREATEST(COALESCE((SELECT last_value FROM pg_sequences WHERE schemaname = 'public' AND sequencename = '{$sequence}'), 1), COALESCE((SELECT MAX(id) FROM public.{$table}), 1)), true)");
        }

        return $mapped;
    }

    private function insertRow(string $table, array $row, array $primaryKey, array $sourceRow, bool $hasNaturalKey): array
    {
        $connection = DB::connection('pgsql');
        $key = [];
        foreach ($primaryKey as $column) {
            $key[$column] = $row[$column] ?? $sourceRow[$column];
        }

        $existing = $connection->table($table)->where($key)->first();
        if ($existing) {
            if ($hasNaturalKey && count($primaryKey) === 1 && $primaryKey[0] === 'id') {
                unset($row['id']);
                $id = $connection->table($table)->insertGetId($row);

                return ['id' => $id];
            }
            throw new RuntimeException("Primary-key conflict in {$table}; no matching natural key exists to prove whether this is a duplicate.");
        }

        $connection->table($table)->insert($row);

        return $key;
    }

    private function mapForeignKeys(array $row, string $table, string $fingerprint): array
    {
        foreach (self::FOREIGN_KEYS[$table] ?? [] as $column => $parentTable) {
            if (! array_key_exists($column, $row) || $row[$column] === null) {
                continue;
            }
            $mapped = DB::connection('pgsql')->table('data_migration_mappings')
                ->where('source_fingerprint', $fingerprint)->where('source_table', $parentTable)
                ->where('source_key', $this->keyJson(['id' => $row[$column]], ['id']))->value('target_key');

            if ($mapped !== null) {
                $row[$column] = json_decode($mapped, true, flags: JSON_THROW_ON_ERROR)['id'];
            } elseif (! DB::connection('pgsql')->table($parentTable)->where('id', $row[$column])->exists()) {
                throw new RuntimeException("Unmapped foreign key {$table}.{$column} references {$parentTable} id {$row[$column]}.");
            }
        }

        return $row;
    }

    private function mapPolymorphicKeys(array $row, string $table, string $fingerprint): array
    {
        $morphs = match ($table) {
            'model_has_roles', 'model_has_permissions' => [['model_type', 'model_id']],
            'personal_access_tokens' => [['tokenable_type', 'tokenable_id']],
            'activity_log' => [['causer_type', 'causer_id'], ['subject_type', 'subject_id']],
            'audit_logs' => [['auditable_type', 'auditable_id']],
            default => [],
        };

        $modelTables = [
            User::class => 'users',
            Booking::class => 'bookings',
            Inventory::class => 'inventories',
            InventoryCategory::class => 'inventory_categories',
            BorrowRequest::class => 'borrow_requests',
            BorrowItem::class => 'borrow_items',
            BorrowReturnEvidence::class => 'borrow_return_evidences',
            Report::class => 'reports',
        ];

        foreach ($morphs as [$typeColumn, $idColumn]) {
            $parentTable = $modelTables[$row[$typeColumn] ?? ''] ?? null;
            if ($parentTable === null || empty($row[$idColumn])) {
                continue;
            }
            $mapped = DB::connection('pgsql')->table('data_migration_mappings')
                ->where('source_fingerprint', $fingerprint)->where('source_table', $parentTable)
                ->where('source_key', $this->keyJson(['id' => $row[$idColumn]], ['id']))->value('target_key');
            if ($mapped !== null) {
                $row[$idColumn] = json_decode($mapped, true, flags: JSON_THROW_ON_ERROR)['id'];
            } elseif (! DB::connection('pgsql')->table($parentTable)->where('id', $row[$idColumn])->exists()) {
                // Keep historical morphs whose subject was force-deleted in the source.
                continue;
            }
        }

        return $row;
    }

    private function mapReturnResponseIds(array $payload, string $fingerprint): array
    {
        if (! isset($payload['request']) || ! is_array($payload['request'])) {
            return $payload;
        }

        $payload['request']['id'] = $this->mappedId('borrow_requests', $payload['request']['id'] ?? null, $fingerprint);
        foreach ($payload['request']['items'] ?? [] as $index => $item) {
            if (! is_array($item)) {
                continue;
            }
            $item['id'] = $this->mappedId('borrow_items', $item['id'] ?? null, $fingerprint);
            $item['inventory_id'] = $this->mappedId('inventories', $item['inventory_id'] ?? null, $fingerprint);
            $payload['request']['items'][$index] = $item;
        }

        return $payload;
    }

    private function mappedId(string $table, mixed $sourceId, string $fingerprint): mixed
    {
        if ($sourceId === null) {
            return null;
        }
        $targetKey = DB::connection('pgsql')->table('data_migration_mappings')
            ->where('source_fingerprint', $fingerprint)->where('source_table', $table)
            ->where('source_key', $this->keyJson(['id' => $sourceId], ['id']))->value('target_key');

        return $targetKey === null ? $sourceId : json_decode($targetKey, true, flags: JSON_THROW_ON_ERROR)['id'];
    }

    private function reconcile(PDO $source, array $tables, string $fingerprint, array $counts): void
    {
        $rows = [];
        foreach ($tables as $table) {
            $sourceCount = (int) $source->query('SELECT COUNT(*) FROM "'.$table.'"')->fetchColumn();
            $mapCount = DB::connection('pgsql')->table('data_migration_mappings')
                ->where('source_fingerprint', $fingerprint)->where('source_table', $table)->count();
            $targetFound = 0;
            foreach (DB::connection('pgsql')->table('data_migration_mappings')
                ->where('source_fingerprint', $fingerprint)->where('source_table', $table)->get(['target_key']) as $mapping) {
                $targetKey = json_decode($mapping->target_key, true, flags: JSON_THROW_ON_ERROR);
                $targetFound += DB::connection('pgsql')->table($table)->where($targetKey)->exists() ? 1 : 0;
            }
            if ($sourceCount !== $mapCount || $sourceCount !== $targetFound || $sourceCount !== ($counts[$table] ?? $sourceCount)) {
                throw new RuntimeException("Final reconciliation failed for {$table}: source={$sourceCount}, mappings={$mapCount}.");
            }
            $rows[] = [$table, $sourceCount, $mapCount, $targetFound, 'OK'];
        }
        $this->table(['Table', 'Source', 'Mapped', 'Target rows found', 'Reconciled'], $rows);
    }

    private function sourceColumns(PDO $source, string $table): array
    {
        return array_column($source->query('PRAGMA table_info("'.$table.'")')->fetchAll(), 'name');
    }

    private function primaryKey(PDO $source, string $table): array
    {
        $columns = $source->query('PRAGMA table_info("'.$table.'")')->fetchAll();
        usort($columns, fn ($a, $b) => $a['pk'] <=> $b['pk']);

        return array_values(array_map(fn ($column) => $column['name'], array_filter($columns, fn ($column) => (int) $column['pk'] > 0)));
    }

    private function keyJson(array $row, array $columns): string
    {
        $key = [];
        foreach ($columns as $column) {
            $key[$column] = $row[$column] ?? null;
        }

        return json_encode($key, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
    }
}

<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PDO;

class MigrateSqliteToSupabase extends Command
{
    protected $signature = 'rvg:migrate-sqlite-to-supabase
        {--sqlite=database/database.sqlite : Path to the source SQLite database}
        {--force : Run without confirmation}';

    protected $description = 'Migrate users, roles, inventory and borrow records from SQLite into Supabase PostgreSQL without data loss or duplicates.';

    public function handle(): int
    {
        $sqlitePath = base_path($this->option('sqlite'));

        if (! is_file($sqlitePath)) {
            $this->error("SQLite database not found at {$sqlitePath}.");

            return self::FAILURE;
        }

        if (config('database.default') !== 'pgsql') {
            $this->error('The default connection must be pgsql (Supabase) to run this migration. Set DB_CONNECTION=pgsql in .env.');

            return self::FAILURE;
        }

        if (! $this->option('force') && ! $this->confirm('Back up both databases before continuing. Continue with the migration?')) {
            $this->warn('Aborted.');

            return self::FAILURE;
        }

        $source = new PDO("sqlite:{$sqlitePath}");
        $source->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $source->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        $target = DB::connection('pgsql');

        $this->info('Migrating roles and permissions...');
        $roleIdMap = $this->migrateRoles($source, $target);
        $permissionIdMap = $this->migratePermissions($source, $target);
        $this->migrateRolePermissions($source, $target, $roleIdMap, $permissionIdMap);

        $this->info('Migrating users...');
        $userIdMap = $this->migrateUsers($source, $target);
        $this->migrateModelRoles($source, $target, $userIdMap, $roleIdMap);

        $this->info('Migrating inventory...');
        $inventoryIdMap = $this->migrateInventories($source, $target);

        $this->info('Migrating borrow requests and items...');
        $requestIdMap = $this->migrateBorrowRequests($source, $target, $userIdMap);
        $this->migrateBorrowItems($source, $target, $requestIdMap, $inventoryIdMap);

        $this->newLine();
        $this->info('Migration complete. Summary:');
        $this->table(['Table', 'Migrated', 'Skipped (already present)'], [
            ['roles', count($roleIdMap), $this->countRows($source, 'roles') - count($roleIdMap)],
            ['permissions', count($permissionIdMap), $this->countRows($source, 'permissions') - count($permissionIdMap)],
            ['users', count($userIdMap), $this->countRows($source, 'users') - count($userIdMap)],
            ['inventories', count($inventoryIdMap), $this->countRows($source, 'inventories') - count($inventoryIdMap)],
            ['borrow_requests', count($requestIdMap), $this->countRows($source, 'borrow_requests') - count($requestIdMap)],
        ]);

        return self::SUCCESS;
    }

    private function migrateRoles(PDO $source, $target): array
    {
        $map = [];

        foreach ($this->all($source, 'SELECT * FROM roles ORDER BY id') as $row) {
            $existing = $target->table('roles')->where('name', $row['name'])->first();

            if ($existing) {
                $map[(int) $row['id']] = (int) $existing->id;

                continue;
            }

            $id = $target->table('roles')->insertGetId([
                'name' => $row['name'],
                'guard_name' => $row['guard_name'] ?? 'web',
                'created_at' => $row['created_at'] ?? now(),
                'updated_at' => $row['updated_at'] ?? now(),
            ]);

            $map[(int) $row['id']] = (int) $id;
        }

        return $map;
    }

    private function migratePermissions(PDO $source, $target): array
    {
        $map = [];

        foreach ($this->all($source, 'SELECT * FROM permissions ORDER BY id') as $row) {
            $existing = $target->table('permissions')->where('name', $row['name'])->first();

            if ($existing) {
                $map[(int) $row['id']] = (int) $existing->id;

                continue;
            }

            $id = $target->table('permissions')->insertGetId([
                'name' => $row['name'],
                'guard_name' => $row['guard_name'] ?? 'web',
                'created_at' => $row['created_at'] ?? now(),
                'updated_at' => $row['updated_at'] ?? now(),
            ]);

            $map[(int) $row['id']] = (int) $id;
        }

        return $map;
    }

    private function migrateRolePermissions(PDO $source, $target, array $roleIdMap, array $permissionIdMap): void
    {
        foreach ($this->all($source, 'SELECT * FROM role_has_permissions') as $row) {
            $roleId = $roleIdMap[(int) $row['role_id']] ?? null;
            $permissionId = $permissionIdMap[(int) $row['permission_id']] ?? null;

            if (! $roleId || ! $permissionId) {
                continue;
            }

            $exists = $target->table('role_has_permissions')
                ->where('role_id', $roleId)
                ->where('permission_id', $permissionId)
                ->exists();

            if ($exists) {
                continue;
            }

            $target->table('role_has_permissions')->insert([
                'role_id' => $roleId,
                'permission_id' => $permissionId,
            ]);
        }
    }

    private function migrateUsers(PDO $source, $target): array
    {
        $map = [];

        foreach ($this->all($source, 'SELECT * FROM users ORDER BY id') as $row) {
            $existing = $target->table('users')->where('email', $row['email'])->first();

            if ($existing) {
                $map[(int) $row['id']] = (int) $existing->id;

                continue;
            }

            $id = $target->table('users')->insertGetId([
                'name' => $row['name'],
                'email' => $row['email'],
                'email_verified_at' => $this->nullable($row['email_verified_at']),
                'password' => $row['password'],
                'remember_token' => $this->nullable($row['remember_token']),
                'phone' => $this->nullable($row['phone'] ?? null),
                'address' => $this->nullable($row['address'] ?? null),
                'avatar_path' => $this->nullable($row['avatar_path'] ?? null),
                'is_active' => (bool) ($row['is_active'] ?? true),
                'last_login_at' => $this->nullable($row['last_login_at'] ?? null),
                'mfa_enabled' => (bool) ($row['mfa_enabled'] ?? false),
                'mfa_secret' => $this->nullable($row['mfa_secret'] ?? null),
                'mfa_type' => $row['mfa_type'] ?? 'email',
                'mfa_verified_at' => $this->nullable($row['mfa_verified_at'] ?? null),
                'mfa_recovery_codes' => $this->jsonColumn($row['mfa_recovery_codes'] ?? null),
                'lock_version' => (int) ($row['lock_version'] ?? 1),
                'created_at' => $this->nullable($row['created_at']) ?? now(),
                'updated_at' => $this->nullable($row['updated_at']) ?? now(),
            ]);

            $map[(int) $row['id']] = (int) $id;
        }

        return $map;
    }

    private function migrateModelRoles(PDO $source, $target, array $userIdMap, array $roleIdMap): void
    {
        foreach ($this->all($source, 'SELECT * FROM model_has_roles') as $row) {
            $roleId = $roleIdMap[(int) $row['role_id']] ?? null;
            $modelId = $userIdMap[(int) $row['model_id']] ?? null;

            if (! $roleId || ! $modelId) {
                continue;
            }

            $exists = $target->table('model_has_roles')
                ->where('role_id', $roleId)
                ->where('model_id', $modelId)
                ->exists();

            if ($exists) {
                continue;
            }

            $target->table('model_has_roles')->insert([
                'role_id' => $roleId,
                'model_type' => User::class,
                'model_id' => $modelId,
            ]);
        }
    }

    private function migrateInventories(PDO $source, $target): array
    {
        $map = [];

        foreach ($this->all($source, 'SELECT * FROM inventories ORDER BY id') as $row) {
            $existing = $target->table('inventories')->where('item_code', $row['item_code'])->first();

            if ($existing) {
                $map[(int) $row['id']] = (int) $existing->id;

                continue;
            }

            $id = $target->table('inventories')->insertGetId([
                'item_code' => $row['item_code'],
                'name' => $row['name'],
                'brand' => $this->nullable($row['brand'] ?? null),
                'description' => $this->nullable($row['description']),
                'category_id' => (int) $row['category_id'],
                'supplier_id' => $row['supplier_id'] ? (int) $row['supplier_id'] : null,
                'quantity' => (int) $row['quantity'],
                'unit' => $row['unit'] ?? 'pcs',
                'unit_cost' => $this->nullable($row['unit_cost']),
                'status' => $row['status'] ?? 'available',
                'condition' => $row['condition'] ?? 'good',
                'location' => $this->nullable($row['location'] ?? null),
                'image_path' => $this->nullable($row['image_path'] ?? null),
                'low_stock_threshold' => (int) ($row['low_stock_threshold'] ?? 5),
                'date_added' => $this->toDate($row['date_added']),
                'is_active' => (bool) ($row['is_active'] ?? true),
                'lock_version' => (int) ($row['lock_version'] ?? 1),
                'created_at' => $this->nullable($row['created_at']) ?? now(),
                'updated_at' => $this->nullable($row['updated_at']) ?? now(),
                'deleted_at' => $this->nullable($row['deleted_at'] ?? null),
            ]);

            $map[(int) $row['id']] = (int) $id;
        }

        return $map;
    }

    private function migrateBorrowRequests(PDO $source, $target, array $userIdMap): array
    {
        $map = [];

        foreach ($this->all($source, 'SELECT * FROM borrow_requests ORDER BY id') as $row) {
            $existing = $target->table('borrow_requests')->where('request_number', $row['request_number'])->first();

            if ($existing) {
                $map[(int) $row['id']] = (int) $existing->id;

                continue;
            }

            $id = $target->table('borrow_requests')->insertGetId([
                'request_number' => $row['request_number'],
                'employee_id' => $userIdMap[(int) $row['employee_id']] ?? null,
                'approved_by' => $row['approved_by'] ? ($userIdMap[(int) $row['approved_by']] ?? null) : null,
                'status' => $row['status'],
                'reason' => $row['reason'],
                'borrow_date' => $this->toDate($row['borrow_date']),
                'due_date' => $this->toDate($row['due_date']),
                'return_date' => $this->toDate($row['return_date'] ?? null),
                'penalty_notes' => $this->nullable($row['penalty_notes'] ?? null),
                'admin_remarks' => $this->nullable($row['admin_remarks'] ?? null),
                'approved_at' => $this->nullable($row['approved_at']),
                'rejected_at' => $this->nullable($row['rejected_at']),
                'borrowed_at' => $this->nullable($row['borrowed_at']),
                'returned_at' => $this->nullable($row['returned_at']),
                'lock_version' => (int) ($row['lock_version'] ?? 1),
                'created_at' => $this->nullable($row['created_at']) ?? now(),
                'updated_at' => $this->nullable($row['updated_at']) ?? now(),
                'deleted_at' => $this->nullable($row['deleted_at'] ?? null),
            ]);

            $map[(int) $row['id']] = (int) $id;
            $this->newRequestIds[(int) $row['id']] = true;
        }

        return $map;
    }

    private function migrateBorrowItems(PDO $source, $target, array $requestIdMap, array $inventoryIdMap): void
    {
        foreach ($this->all($source, 'SELECT * FROM borrow_items ORDER BY id') as $row) {
            $sourceRequestId = (int) $row['borrow_request_id'];

            // Only insert items for requests we just created; existing requests
            // already have their items from a previous run.
            if (! isset($this->newRequestIds[$sourceRequestId])) {
                continue;
            }

            $requestId = $requestIdMap[$sourceRequestId] ?? null;
            $inventoryId = $inventoryIdMap[(int) $row['inventory_id']] ?? null;

            if (! $requestId || ! $inventoryId) {
                continue;
            }

            $isReturned = (bool) ($row['is_returned'] ?? false);
            $status = $target->table('borrow_requests')->where('id', $requestId)->value('status') ?? 'pending';
            $reservedQuantity = (! $isReturned && in_array($status, ['approved', 'borrowed'], true))
                ? (int) $row['quantity']
                : 0;

            $target->table('borrow_items')->insert([
                'borrow_request_id' => $requestId,
                'inventory_id' => $inventoryId,
                'quantity' => (int) $row['quantity'],
                'reserved_quantity' => $reservedQuantity,
                'condition_borrowed' => $row['condition_borrowed'] ?? 'good',
                'condition_returned' => $this->nullable($row['condition_returned'] ?? null),
                'is_returned' => $isReturned,
                'returned_at' => $this->nullable($row['returned_at'] ?? null),
                'damage_notes' => $this->nullable($row['damage_notes'] ?? null),
                'created_at' => $this->nullable($row['created_at']) ?? now(),
                'updated_at' => $this->nullable($row['updated_at']) ?? now(),
            ]);
        }
    }

    private function all(PDO $source, string $sql): array
    {
        return $source->query($sql)->fetchAll();
    }

    private function countRows(PDO $source, string $table): int
    {
        return (int) $source->query("SELECT COUNT(*) FROM {$table}")->fetchColumn();
    }

    private function nullable(mixed $value): mixed
    {
        if ($value === null || $value === '') {
            return null;
        }

        return $value;
    }

    private function toDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $timestamp = strtotime((string) $value);

        return $timestamp !== false ? date('Y-m-d', $timestamp) : substr((string) $value, 0, 10);
    }

    private function jsonColumn(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);

            return $decoded !== null ? json_encode($decoded) : json_encode([$value]);
        }

        return json_encode($value);
    }

    private array $newRequestIds = [];
}

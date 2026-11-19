<?php

namespace App\Console\Commands;

use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CreateUserAccount extends Command
{
    protected $signature = 'app:create-user
                            {email : The email address for the user}
                            {--role=employee : The role to assign (admin or employee)}
                            {--password= : The user password}
                            {--name= : The display name of the user}';

    protected $description = 'Create or update an admin or employee user account';

    public function handle(): int
    {
        $email = strtolower(trim((string) $this->argument('email')));
        $roleName = strtolower(trim((string) $this->option('role')));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error("Invalid email address: {$email}");
            return self::FAILURE;
        }

        if (! in_array($roleName, ['admin', 'employee'], true)) {
            $this->error("Invalid role '{$roleName}'. Allowed roles are 'admin' or 'employee'.");
            return self::FAILURE;
        }

        $password = (string) $this->option('password');
        if ($password === '') {
            $password = (string) $this->secret('Password');
            $confirmation = (string) $this->secret('Confirm password');
            if ($password !== $confirmation) {
                $this->error('Passwords do not match.');
                return self::FAILURE;
            }
        }

        if (strlen($password) < 8) {
            $this->error('Password must be at least 8 characters long.');
            return self::FAILURE;
        }

        $name = (string) $this->option('name');
        if ($name === '') {
            $name = $roleName === 'admin' ? 'Administrator' : 'Employee User';
        }

        DB::transaction(function () use ($email, $password, $name, $roleName): void {
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

            if (Permission::count() === 0) {
                $this->call('db:seed', [
                    '--class' => RoleAndPermissionSeeder::class,
                    '--force' => true,
                ]);
            }

            $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
            $employeeRole = Role::firstOrCreate(['name' => 'employee', 'guard_name' => 'web']);

            $targetRole = $roleName === 'admin' ? $adminRole : $employeeRole;

            if ($roleName === 'admin') {
                $adminRole->syncPermissions(Permission::where('guard_name', 'web')->get());
            } else {
                $employeeRole->syncPermissions(
                    Permission::whereIn('name', [
                        'view bookings',
                        'view inventories',
                        'create borrow requests',
                        'view borrow requests',
                        'return borrowed items',
                    ])->where('guard_name', 'web')->get()
                );
            }

            $user = User::where('email', $email)->first();
            if ($user) {
                $user->update([
                    'name' => $name,
                    'password' => $password,
                    'is_active' => true,
                    'email_verified_at' => $user->email_verified_at ?? now(),
                ]);
            } else {
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'password' => $password,
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]);
            }

            $user->syncRoles([$targetRole]);
        });

        $this->info("User successfully created/updated!");
        $this->table(
            ['Field', 'Value'],
            [
                ['Name', $name],
                ['Email', $email],
                ['Role', $roleName],
                ['Status', 'Active'],
            ]
        );

        return self::SUCCESS;
    }
}

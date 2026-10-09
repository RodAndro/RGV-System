<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CreateAdminAccount extends Command
{
    protected $signature = 'app:create-admin {email} {--name=RGV Administrator}';

    protected $description = 'Create an administrator account in the configured database';

    public function handle(): int
    {
        $email = strtolower(trim((string) $this->argument('email')));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Enter a valid email address.');

            return self::FAILURE;
        }

        if (User::where('email', $email)->exists()) {
            $this->error('An account with this email already exists. No changes were made.');

            return self::FAILURE;
        }

        $password = $this->secret('New password');
        $confirmation = $this->secret('Confirm new password');

        if (! is_string($password) || strlen($password) < 12 || $password !== $confirmation) {
            $this->error('Passwords must match and be at least 12 characters long. No account was created.');

            return self::FAILURE;
        }

        DB::transaction(function () use ($email, $password): void {
            $adminRole = Role::firstOrCreate([
                'name' => 'admin',
                'guard_name' => 'web',
            ]);

            $user = User::create([
                'name' => (string) $this->option('name'),
                'email' => $email,
                'password' => $password,
                'is_active' => true,
            ]);

            $user->assignRole($adminRole);
            $adminRole->syncPermissions(Permission::where('guard_name', 'web')->get());
        });

        $this->info("Administrator account created for {$email}.");

        return self::SUCCESS;
    }
}
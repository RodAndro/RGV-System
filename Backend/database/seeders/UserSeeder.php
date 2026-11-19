<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException('Demo accounts may only be seeded in local or testing environments.');
        }

        $this->seedDemoAccount(
            email: env('DEMO_ADMIN_EMAIL'),
            password: env('DEMO_ADMIN_PASSWORD'),
            name: 'Demo Administrator',
            role: 'admin',
        );

        $this->seedDemoAccount(
            email: env('DEMO_EMPLOYEE_EMAIL'),
            password: env('DEMO_EMPLOYEE_PASSWORD'),
            name: 'Demo Employee',
            role: 'employee',
        );
    }

    private function seedDemoAccount(?string $email, ?string $password, string $name, string $role): void
    {
        if (! filled($email) || ! filled($password)) {
            $this->command?->warn("Skipped {$role} demo account; set its DEMO_* email and password in local .env.");

            return;
        }

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'is_active' => true,
            ]
        );
        $user->assignRole($role);
    }
}

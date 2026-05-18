<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@rgvtech.com'],
            [
                'name' => 'RGV Multi-Tech Services',
                'email' => 'admin@rgvtech.com',
                'password' => Hash::make('admin123'),
                'phone' => '09123456789',
                'address' => '123 Tech Park, Makati City, Philippines',
                'is_active' => true,
            ]
        );
        $admin->assignRole('admin');

        // Create employee users with realistic data
        $employees = [
            [
                'name' => 'Juan Dela Cruz',
                'email' => 'juan.delacruz@rgvtech.com',
                'password' => 'employee123',
                'phone' => '09171234567',
                'address' => '456 Mabini St, Quezon City, Philippines',
            ],
            [
                'name' => 'Ana Garcia',
                'email' => 'ana.garcia@rgvtech.com',
                'password' => 'employee123',
                'phone' => '09182345678',
                'address' => '789 Rizal Ave, Manila, Philippines',
            ],
            [
                'name' => 'Carlos Mendoza',
                'email' => 'carlos.mendoza@rgvtech.com',
                'password' => 'employee123',
                'phone' => '09193456789',
                'address' => '321 Bonifacio St, Pasig City, Philippines',
            ],
        ];

        foreach ($employees as $employee) {
            $user = User::firstOrCreate(
                ['email' => $employee['email']],
                array_merge($employee, [
                    'password' => Hash::make($employee['password']),
                    'is_active' => true,
                ])
            );
            $user->assignRole('employee');
        }
    }
}

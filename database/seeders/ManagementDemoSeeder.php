<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\BorrowItem;
use App\Models\BorrowRequest;
use App\Models\Inventory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;

class ManagementDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException('Sample data may only be seeded in local or testing environments.');
        }

        $employee = $this->demoEmployee();
        $admin = User::role('admin')->first();

        $this->seedBookings($employee);
        $this->seedBorrowRequests($employee, $admin);
    }

    private function demoEmployee(): User
    {
        $email = env('DEMO_EMPLOYEE_EMAIL', 'demo-employee@example.test');
        $employee = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => 'Demo Employee',
                'password' => Hash::make(env('DEMO_EMPLOYEE_PASSWORD') ?: Str::random(64)),
                'phone' => '09171234000',
                'address' => 'Cebu City, Cebu',
                'is_active' => true,
            ],
        );

        if (! $employee->hasRole('employee')) {
            if ($employee->hasRole('admin')) {
                throw new RuntimeException('The configured demo employee email belongs to an admin account.');
            }

            $employee->assignRole('employee');
        }

        return $employee;
    }

    private function seedBookings(User $employee): void
    {
        $bookings = [
            [
                'reference_number' => 'DEMO-BK-001',
                'full_name' => 'Marlon Reyes',
                'email' => 'marlon.reyes@example.test',
                'contact_number' => '09171234001',
                'address' => 'Mandaue City, Cebu',
                'preferred_date' => today()->subDays(18)->toDateString(),
                'preferred_time' => '09:00:00',
                'purpose_category' => 'equipment-repair',
                'reason' => 'The walk-in chiller is not maintaining its set temperature.',
                'status' => 'completed',
                'employee_id' => $employee->id,
                'remarks' => 'Compressor relay replaced and unit tested.',
                'approved_at' => now()->subDays(20),
                'completed_at' => now()->subDays(17),
            ],
            [
                'reference_number' => 'DEMO-BK-002',
                'full_name' => 'Liza Santos',
                'email' => 'liza.santos@example.test',
                'contact_number' => '09171234002',
                'address' => 'Lapu-Lapu City, Cebu',
                'preferred_date' => today()->addDays(3)->toDateString(),
                'preferred_time' => '10:30:00',
                'purpose_category' => 'maintenance',
                'reason' => 'Quarterly preventive maintenance for two split-type air conditioners.',
                'status' => 'approved',
                'employee_id' => $employee->id,
                'remarks' => 'Technician assigned; customer confirmed site access.',
                'approved_at' => now()->subDay(),
            ],
            [
                'reference_number' => 'DEMO-BK-003',
                'full_name' => 'Carlo Mendoza',
                'email' => 'carlo.mendoza@example.test',
                'contact_number' => '09171234003',
                'address' => 'Cebu City, Cebu',
                'preferred_date' => today()->addDays(5)->toDateString(),
                'preferred_time' => '13:00:00',
                'purpose_category' => 'tool-rental',
                'reason' => 'Requesting a tile cutter for a small commercial renovation.',
                'status' => 'pending',
            ],
            [
                'reference_number' => 'DEMO-BK-004',
                'full_name' => 'Ana Villanueva',
                'email' => 'ana.villanueva@example.test',
                'contact_number' => '09171234004',
                'address' => 'Talisay City, Cebu',
                'preferred_date' => today()->subDays(8)->toDateString(),
                'preferred_time' => '14:00:00',
                'purpose_category' => 'consultation',
                'reason' => 'Needs an on-site assessment for a new kitchen exhaust installation.',
                'status' => 'rejected',
                'remarks' => 'Customer requested to reschedule outside the service area.',
                'rejected_at' => now()->subDays(9),
            ],
            [
                'reference_number' => 'DEMO-BK-005',
                'full_name' => 'Ramon Garcia',
                'email' => 'ramon.garcia@example.test',
                'contact_number' => '09171234005',
                'address' => 'Consolacion, Cebu',
                'preferred_date' => today()->addDays(12)->toDateString(),
                'preferred_time' => '15:30:00',
                'purpose_category' => 'installation',
                'reason' => 'Installation and testing of a new commercial exhaust fan.',
                'status' => 'pending',
            ],
            [
                'reference_number' => 'DEMO-BK-006',
                'full_name' => 'Joyce Lim',
                'email' => 'joyce.lim@example.test',
                'contact_number' => '09171234006',
                'address' => 'Cebu City, Cebu',
                'preferred_date' => today()->subDays(35)->toDateString(),
                'preferred_time' => '08:30:00',
                'purpose_category' => 'equipment-repair',
                'reason' => 'Commercial freezer requires inspection after intermittent shutdowns.',
                'status' => 'completed',
                'employee_id' => $employee->id,
                'remarks' => 'Loose electrical connection repaired; cooling cycle verified.',
                'approved_at' => now()->subDays(37),
                'completed_at' => now()->subDays(34),
            ],
        ];

        foreach ($bookings as $booking) {
            Booking::updateOrCreate(
                ['reference_number' => $booking['reference_number']],
                ['employee_id' => null, ...$booking],
            );
        }
    }

    private function seedBorrowRequests(User $employee, ?User $admin): void
    {
        $inventories = Inventory::query()
            ->whereIn('item_code', ['RGV-001', 'RGV-002', 'RGV-003', 'RGV-006'])
            ->get()
            ->keyBy('item_code');

        if ($inventories->count() !== 4) {
            $this->command?->warn('Skipped demo borrow requests because the sample inventory is incomplete.');

            return;
        }

        $requests = [
            [
                'request_number' => 'DEMO-BR-001',
                'status' => 'returned',
                'reason' => 'Used for the completed tile replacement job at the service center.',
                'borrow_date' => today()->subDays(28)->toDateString(),
                'due_date' => today()->subDays(21)->toDateString(),
                'return_date' => today()->subDays(22)->toDateString(),
                'return_location' => 'Main Warehouse',
                'approved_by' => $admin?->id,
                'approved_at' => now()->subDays(29),
                'borrowed_at' => now()->subDays(28),
                'returned_at' => now()->subDays(22),
                'items' => [
                    ['item_code' => 'RGV-001', 'quantity' => 1],
                    ['item_code' => 'RGV-003', 'quantity' => 1],
                ],
            ],
            [
                'request_number' => 'DEMO-BR-002',
                'status' => 'rejected',
                'reason' => 'Requested for an external project while the tool was reserved for maintenance.',
                'borrow_date' => today()->addDays(2)->toDateString(),
                'due_date' => today()->addDays(9)->toDateString(),
                'rejected_at' => now()->subDay(),
                'admin_remarks' => 'Please choose another date after the scheduled maintenance.',
                'items' => [
                    ['item_code' => 'RGV-002', 'quantity' => 1],
                ],
            ],
            [
                'request_number' => 'DEMO-BR-003',
                'status' => 'returned',
                'reason' => 'Used for a scheduled concrete base repair at the service yard.',
                'borrow_date' => today()->subDays(12)->toDateString(),
                'due_date' => today()->subDays(5)->toDateString(),
                'return_date' => today()->subDays(6)->toDateString(),
                'return_location' => 'Main Warehouse',
                'approved_by' => $admin?->id,
                'approved_at' => now()->subDays(13),
                'borrowed_at' => now()->subDays(12),
                'returned_at' => now()->subDays(6),
                'items' => [
                    ['item_code' => 'RGV-006', 'quantity' => 1],
                ],
            ],
            [
                'request_number' => 'DEMO-BR-004',
                'status' => 'cancelled',
                'reason' => 'The project schedule changed and the tool is no longer needed.',
                'borrow_date' => today()->addDays(5)->toDateString(),
                'due_date' => today()->addDays(12)->toDateString(),
                'cancelled_at' => now()->subDay(),
                'items' => [
                    ['item_code' => 'RGV-002', 'quantity' => 1],
                ],
            ],
        ];

        foreach ($requests as $requestData) {
            $items = $requestData['items'];
            unset($requestData['items']);

            $borrowRequest = BorrowRequest::updateOrCreate(
                ['request_number' => $requestData['request_number']],
                [...$requestData, 'employee_id' => $employee->id],
            );

            foreach ($items as $itemData) {
                $inventory = $inventories->get($itemData['item_code']);
                $isReturned = $requestData['status'] === 'returned';

                BorrowItem::updateOrCreate(
                    ['borrow_request_id' => $borrowRequest->id, 'inventory_id' => $inventory->id],
                    [
                        'quantity' => $itemData['quantity'],
                        'reserved_quantity' => 0,
                        'condition_borrowed' => 'good',
                        'condition_returned' => $isReturned ? 'good' : null,
                        'is_returned' => $isReturned,
                        'returned_at' => $isReturned ? $requestData['returned_at'] : null,
                    ],
                );
            }
        }
    }
}

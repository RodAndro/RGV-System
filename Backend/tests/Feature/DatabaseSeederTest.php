<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_local_sample_seeder_populates_linked_management_data(): void
    {
        $demoEmployeeEmail = env('DEMO_EMPLOYEE_EMAIL') ?: 'demo-employee@example.test';

        $this->seed();

        $this->assertDatabaseHas('roles', ['name' => 'admin']);
        $this->assertDatabaseHas('roles', ['name' => 'employee']);
        $this->assertDatabaseHas('inventory_categories', ['slug' => 'tools']);
        $this->assertDatabaseHas('inventories', ['item_code' => 'RGV-001']);
        $this->assertDatabaseHas('bookings', ['reference_number' => 'DEMO-BK-001']);
        $this->assertDatabaseHas('borrow_requests', ['request_number' => 'DEMO-BR-001', 'status' => 'returned']);
        $this->assertDatabaseHas('users', ['email' => $demoEmployeeEmail]);

        $employeeId = DB::table('users')->where('email', $demoEmployeeEmail)->value('id');
        $borrowRequestId = DB::table('borrow_requests')->where('request_number', 'DEMO-BR-001')->value('id');

        $this->assertDatabaseHas('borrow_requests', [
            'id' => $borrowRequestId,
            'employee_id' => $employeeId,
        ]);
        $this->assertSame(12, DB::table('inventories')->count());
        $this->assertSame(6, DB::table('bookings')->count());
        $this->assertSame(4, DB::table('borrow_requests')->count());
        $this->assertSame(5, DB::table('borrow_items')->count());

        $this->seed();

        $this->assertSame(12, DB::table('inventories')->count());
        $this->assertSame(6, DB::table('bookings')->count());
        $this->assertSame(4, DB::table('borrow_requests')->count());
        $this->assertSame(5, DB::table('borrow_items')->count());
    }
}

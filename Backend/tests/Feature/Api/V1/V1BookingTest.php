<?php

namespace Tests\Feature\Api\V1;

use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\PermissionRegistrar;
use Tests\Concerns\WithRoles;
use Tests\TestCase;

class V1BookingTest extends TestCase
{
    use RefreshDatabase;
    use WithRoles;

    protected function setUp(): void
    {
        parent::setUp();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->ensureRoles();
    }

    public function test_index_requires_authentication(): void
    {
        $this->getJson('/api/v1/bookings')
            ->assertStatus(401)
            ->assertJsonPath('error.code', 'UNAUTHENTICATED');
    }

    public function test_employee_cannot_list_bookings(): void
    {
        Sanctum::actingAs($this->createEmployee(), ['access']);

        $this->getJson('/api/v1/bookings')
            ->assertStatus(403)
            ->assertJsonPath('error.code', 'FORBIDDEN');
    }

    public function test_admin_can_list_bookings_with_meta_and_filters(): void
    {
        Sanctum::actingAs($this->createAdmin(), ['access']);
        Booking::factory()->create(['status' => 'pending']);
        Booking::factory()->create(['status' => 'approved']);

        $this->getJson('/api/v1/bookings')
            ->assertOk()
            ->assertJsonStructure(['data' => [['id', 'reference_number', 'status']], 'meta'])
            ->assertJsonPath('meta.total', 2);

        $this->getJson('/api/v1/bookings?status=approved')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.status', 'approved');
    }

    public function test_admin_can_show_a_booking(): void
    {
        Sanctum::actingAs($this->createAdmin(), ['access']);
        $booking = Booking::factory()->create();

        $this->getJson('/api/v1/bookings/'.$booking->id)
            ->assertOk()
            ->assertJsonPath('data.id', $booking->id);
    }

    public function test_show_returns_404_for_missing_booking(): void
    {
        Sanctum::actingAs($this->createAdmin(), ['access']);

        $this->getJson('/api/v1/bookings/999999')
            ->assertStatus(404)
            ->assertJsonPath('error.code', 'NOT_FOUND');
    }
}

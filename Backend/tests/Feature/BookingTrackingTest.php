<?php

namespace Tests\Feature;

use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\WithRoles;
use Tests\TestCase;

class BookingTrackingTest extends TestCase
{
    use RefreshDatabase;
    use WithRoles;

    public function test_booking_page_renders_with_track_section_and_booking_form_by_default(): void
    {
        $response = $this->get(route('booking.form'));

        $response->assertOk()
            ->assertSee('Track Your Booking')
            ->assertSee('Book an Appointment')
            ->assertSee('track_reference_number')
            ->assertSee('booking-form');
    }

    public function test_booking_search_json_returns_complete_details_for_valid_reference(): void
    {
        $booking = Booking::factory()->create([
            'reference_number' => 'BK-VALID-12345',
            'full_name' => 'Maria Santos',
            'email' => 'maria@example.com',
            'contact_number' => '09171234567',
            'purpose_category' => 'equipment-repair',
            'address' => '123 Rizal Street, Manila',
            'preferred_date' => today()->addDays(3)->toDateString(),
            'preferred_time' => '14:30:00',
            'reason' => 'Need emergency repair for welding machine',
            'status' => 'pending',
        ]);

        $response = $this->postJson(route('booking.search'), [
            'reference_number' => 'BK-VALID-12345',
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'booking' => [
                    'reference_number' => 'BK-VALID-12345',
                    'full_name' => 'Maria Santos',
                    'email' => 'maria@example.com',
                    'contact_number' => '09171234567',
                    'purpose_category' => 'equipment-repair',
                    'purpose_category_label' => 'Equipment Repair',
                    'address' => '123 Rizal Street, Manila',
                    'preferred_time' => '2:30 PM',
                    'reason' => 'Need emergency repair for welding machine',
                    'status' => 'pending',
                    'status_label' => 'Pending',
                ],
            ]);
    }

    public function test_booking_search_get_request_also_returns_json(): void
    {
        $booking = Booking::factory()->create([
            'reference_number' => 'BK-GET-TEST-99',
            'full_name' => 'Juan Dela Cruz',
            'status' => 'approved',
        ]);

        $response = $this->getJson(route('booking.search', ['reference_number' => 'BK-GET-TEST-99']));

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'booking' => [
                    'reference_number' => 'BK-GET-TEST-99',
                    'full_name' => 'Juan Dela Cruz',
                    'status' => 'approved',
                    'status_label' => 'Approved',
                ],
            ]);
    }

    public function test_booking_search_returns_404_for_non_existent_reference(): void
    {
        $response = $this->postJson(route('booking.search'), [
            'reference_number' => 'BK-NONEXISTENT',
        ]);

        $response->assertNotFound()
            ->assertJson([
                'success' => false,
            ])
            ->assertJsonPath('message', fn ($msg) => str_contains($msg, 'BK-NONEXISTENT'));
    }

    public function test_booking_search_validates_empty_input(): void
    {
        $response = $this->postJson(route('booking.search'), [
            'reference_number' => '',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_booking_page_renders_tracked_booking_when_reference_query_parameter_is_provided(): void
    {
        $booking = Booking::factory()->create([
            'reference_number' => 'BK-PAGE-PARAM-TEST',
            'full_name' => 'Pedro Penduko',
            'email' => 'pedro@example.com',
            'status' => 'pending',
        ]);

        $response = $this->get(route('booking.form', ['reference' => 'BK-PAGE-PARAM-TEST']));

        $response->assertOk()
            ->assertSee('BK-PAGE-PARAM-TEST')
            ->assertSee('Pedro Penduko')
            ->assertSee('pedro@example.com')
            ->assertSee('Booking Details');
    }

    public function test_booking_page_shows_error_when_invalid_reference_query_is_provided(): void
    {
        $response = $this->get(route('booking.form', ['reference' => 'BK-DOES-NOT-EXIST']));

        $response->assertOk()
            ->assertSee('No booking found with reference number')
            ->assertSee('BK-DOES-NOT-EXIST')
            ->assertSee('Book an Appointment');
    }

    public function test_tracking_never_modifies_or_creates_bookings(): void
    {
        $booking = Booking::factory()->create([
            'reference_number' => 'BK-IMMUTABLE-TEST',
            'status' => 'pending',
            'full_name' => 'Original Name',
        ]);

        $initialCount = Booking::count();

        // Perform multiple tracking lookups
        $this->postJson(route('booking.search'), ['reference_number' => 'BK-IMMUTABLE-TEST'])->assertOk();
        $this->postJson(route('booking.search'), ['reference_number' => 'BK-DOES-NOT-EXIST'])->assertNotFound();
        $this->get(route('booking.form', ['reference' => 'BK-IMMUTABLE-TEST']))->assertOk();

        $this->assertSame($initialCount, Booking::count());
        $fresh = $booking->fresh();
        $this->assertSame('Original Name', $fresh->full_name);
        $this->assertSame('pending', $fresh->status);
    }

    public function test_booking_submission_rejects_time_outside_office_hours(): void
    {
        $payload = [
            'full_name' => 'Early Bird',
            'email' => 'early@example.com',
            'contact_number' => '09171234567',
            'address' => '123 Main St',
            'preferred_date' => today()->addDays(2)->toDateString(),
            'preferred_time' => '06:30', // Before 8:00 AM
            'purpose_category' => 'consultation',
            'reason' => 'Too early',
        ];

        $response = $this->post(route('booking.store'), $payload);
        $response->assertSessionHasErrors(['preferred_time']);

        $payload['preferred_time'] = '19:00'; // After 5:00 PM
        $response2 = $this->post(route('booking.store'), $payload);
        $response2->assertSessionHasErrors(['preferred_time']);
    }
}

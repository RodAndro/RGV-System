<?php

namespace Tests\Feature;

use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Http\UploadedFile;
use Tests\Concerns\WithRoles;
use Tests\TestCase;

class BookingTimeDisplayTest extends TestCase
{
    use RefreshDatabase;
    use WithRoles;

    public function test_tracking_formats_booking_time_without_changing_the_stored_time(): void
    {
        $booking = Booking::factory()->create([
            'reference_number' => 'BK-TIME-DISPLAY-TEST',
            'preferred_date' => today()->addDays(2)->toDateString(),
            'preferred_time' => '18:30:00',
        ]);

        $this->get(route('booking.track', $booking->reference_number))
            ->assertOk()
            ->assertSee('6:30 PM')
            ->assertDontSee('18:30:00');

        $storedTime = DB::table('bookings')
            ->where('id', $booking->id)
            ->value('preferred_time');

        $this->assertSame('18:30:00', Carbon::parse($storedTime)->format('H:i:s'));
    }

    public function test_booking_submission_ignores_uploaded_attachments(): void
    {
        Notification::fake();
        $this->createAdmin();

        $this->post(route('booking.store'), [
            'full_name' => 'Test Customer',
            'email' => 'customer@example.test',
            'contact_number' => '09170000000',
            'address' => 'Test address',
            'preferred_date' => today()->addDays(2)->toDateString(),
            'preferred_time' => '10:00',
            'purpose_category' => 'consultation',
            'reason' => 'Test booking request',
            'attachment' => UploadedFile::fake()->create('supporting-document.pdf', 10, 'application/pdf'),
        ])->assertRedirect();

        $booking = Booking::where('email', 'customer@example.test')->firstOrFail();
        $this->assertNull($booking->attachment_path);
    }

    public function test_admin_approval_is_persisted_and_visible_on_tracking_page(): void
    {
        Notification::fake();
        $admin = $this->createAdmin();
        $booking = Booking::factory()->create([
            'reference_number' => 'BK-ADMIN-APPROVAL-TEST',
            'status' => 'pending',
            'preferred_date' => '2026-10-05',
            'preferred_time' => '18:30:00',
        ]);
        Cache::put('admin.stats.bookings', ['pending' => 1], now()->addMinute());

        $this->actingAs($admin)
            ->post(route('admin.bookings.approve', $booking))
            ->assertRedirect();

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'approved',
        ]);
        $this->assertFalse(Cache::has('admin.stats.bookings'));

        $this->get(route('admin.bookings.show', $booking))
            ->assertOk()
            ->assertSee('6:30 PM')
            ->assertDontSee('18:30:00');

        $this->get(route('admin.bookings.calendar'))
            ->assertOk()
            ->assertSee('2026-10-05T18:30:00', false);

        $this->get(route('booking.track', $booking->reference_number))
            ->assertOk()
            ->assertSee('Approved')
            ->assertSee('6:30 PM');
    }
}

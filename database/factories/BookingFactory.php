<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        $purposeCategories = [
            'Equipment Repair', 'Tool Rental', 'Technical Consultation', 'Maintenance Service',
            'Installation', 'Equipment Testing', 'Calibration Service', 'Emergency Repair'
        ];
        
        $status = fake()->randomElement(['pending', 'pending', 'pending', 'approved', 'approved', 'completed', 'rejected', 'cancelled']);
        
        return [
            'reference_number' => 'RGV-' . date('Y') . '-' . strtoupper(fake()->unique()->bothify('???###')),
            'full_name' => fake()->name(),
            'email' => fake()->email(),
            'contact_number' => '09' . fake()->numberBetween(100000000, 999999999),
            'address' => fake()->address(),
            'preferred_date' => fake()->dateTimeBetween('now', '+60 days'),
            'preferred_time' => fake()->randomElement(['08:00', '09:00', '10:00', '11:00', '13:00', '14:00', '15:00', '16:00']),
            'purpose_category' => fake()->randomElement($purposeCategories),
            'reason' => fake()->sentence(15),
            'status' => $status,
            'employee_id' => User::whereHas('roles', function ($q) {
                $q->where('name', 'employee');
            })->inRandomOrder()->first()?->id,
            'remarks' => fake()->optional(0.3)->sentence(),
            'approved_at' => $status === 'approved' || $status === 'completed' ? fake()->dateTimeBetween('-30 days', 'now') : null,
            'rejected_at' => $status === 'rejected' ? fake()->dateTimeBetween('-30 days', 'now') : null,
            'completed_at' => $status === 'completed' ? fake()->dateTimeBetween('-30 days', 'now') : null,
            'cancelled_at' => $status === 'cancelled' ? fake()->dateTimeBetween('-30 days', 'now') : null,
        ];
    }
}

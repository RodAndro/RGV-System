<?php

namespace Database\Factories;

use App\Models\BorrowRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BorrowRequestFactory extends Factory
{
    protected $model = BorrowRequest::class;

    public function definition(): array
    {
        $status = fake()->randomElement(['pending', 'pending', 'pending', 'approved', 'approved', 'borrowed', 'returned', 'rejected']);
        
        $borrowDate = fake()->dateTimeBetween('-60 days', 'now');
        $dueDate = (clone $borrowDate)->modify('+'.fake()->numberBetween(7, 30).' days');
        
        return [
            'request_number' => 'BR-' . date('Ym') . '-' . strtoupper(fake()->unique()->bothify('???###')),
            'employee_id' => User::whereHas('roles', function ($q) {
                $q->where('name', 'employee');
            })->inRandomOrder()->first()?->id,
            'approved_by' => ($status === 'approved' || $status === 'borrowed' || $status === 'returned') ? User::whereHas('roles', function ($q) {
                $q->where('name', 'admin');
            })->inRandomOrder()->first()?->id : null,
            'status' => $status,
            'reason' => fake()->sentence(12),
            'borrow_date' => $borrowDate,
            'due_date' => $dueDate,
            'return_date' => $status === 'returned' ? fake()->dateTimeBetween($borrowDate, $dueDate) : null,
            'penalty_notes' => $status === 'returned' && fake()->boolean(20) ? fake()->sentence() : null,
            'admin_remarks' => ($status === 'approved' || $status === 'rejected') ? fake()->optional(0.5)->sentence() : null,
            'approved_at' => ($status === 'approved' || $status === 'borrowed' || $status === 'returned') ? fake()->dateTimeBetween($borrowDate, 'now') : null,
            'rejected_at' => $status === 'rejected' ? fake()->dateTimeBetween($borrowDate, 'now') : null,
            'borrowed_at' => ($status === 'borrowed' || $status === 'returned') ? fake()->dateTimeBetween($borrowDate, 'now') : null,
            'returned_at' => $status === 'returned' ? fake()->dateTimeBetween($borrowDate, $dueDate) : null,
        ];
    }
}

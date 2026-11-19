<?php

namespace Database\Factories;

use App\Models\BorrowItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class BorrowItemFactory extends Factory
{
    protected $model = BorrowItem::class;

    public function definition(): array
    {
        return [
            'quantity' => fake()->numberBetween(1, 5),
            'condition_borrowed' => fake()->randomElement(['excellent', 'good', 'fair']),
            'condition_returned' => fake()->optional(0.5)->randomElement(['excellent', 'good', 'fair', 'poor']),
            'is_returned' => fake()->boolean(40),
            'returned_at' => fake()->optional(0.4)->dateTimeBetween('-30 days', 'now'),
            'damage_notes' => fake()->optional()->sentence(),
        ];
    }
}

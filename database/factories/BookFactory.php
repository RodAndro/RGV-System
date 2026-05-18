<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\InventoryCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BookFactory extends Factory
{
    protected $model = Book::class;

    private static array $categoryIds = [];
    private static int $sequence = 100000000;

    public function definition(): array
    {
        $format = fake()->randomElement(['paperback', 'hardcover', 'ebook', 'audiobook']);
        $title = fake()->randomElement([
            'Practical Systems Design',
            'Field Guide to Reliable Operations',
            'Modern Inventory Patterns',
            'Applied Service Engineering',
            'Distributed Teams Handbook',
            'Maintenance Planning Essentials',
        ]) . ' ' . fake()->unique()->bothify('Vol ###');

        $isbn13 = self::isbn13();

        return [
            'isbn' => substr($isbn13, 0, 3) . '-' . substr($isbn13, 3, 1) . '-' . substr($isbn13, 4, 3) . '-' . substr($isbn13, 7, 5) . '-' . substr($isbn13, 12, 1),
            'isbn13' => $isbn13,
            'title' => $title,
            'slug' => Str::slug($title) . '-' . Str::lower(fake()->unique()->bothify('??###')),
            'category_id' => fake()->randomElement(self::categoryIds()),
            'author' => fake()->name(),
            'publisher' => fake()->company(),
            'format' => $format,
            'price' => self::priceForFormat($format),
            'stock' => fake()->numberBetween(0, 500),
            'sales_count' => fake()->numberBetween(0, 100000),
            'rating' => fake()->randomFloat(2, 2.5, 5),
            'description' => fake()->paragraph(4),
            'is_active' => fake()->boolean(94),
            'published_at' => fake()->dateTimeBetween('-20 years', 'now'),
        ];
    }

    public static function isbn13(?int $seed = null): string
    {
        $number = ($seed ?? self::$sequence++) % 1000000000;
        $base = '978' . str_pad((string) $number, 9, '0', STR_PAD_LEFT);
        $sum = 0;

        for ($i = 0; $i < 12; $i++) {
            $sum += (int) $base[$i] * ($i % 2 === 0 ? 1 : 3);
        }

        $check = (10 - ($sum % 10)) % 10;

        return $base . $check;
    }

    public static function priceForFormat(string $format): float
    {
        return match ($format) {
            'hardcover' => fake()->randomFloat(2, 24.99, 89.99),
            'ebook' => fake()->randomFloat(2, 4.99, 29.99),
            'audiobook' => fake()->randomFloat(2, 9.99, 49.99),
            default => fake()->randomFloat(2, 12.99, 59.99),
        };
    }

    public static function categoryIds(): array
    {
        if (self::$categoryIds === []) {
            self::$categoryIds = InventoryCategory::query()->pluck('id')->all();

            if (self::$categoryIds === []) {
                self::$categoryIds[] = InventoryCategory::create([
                    'name' => 'Books',
                    'slug' => 'books',
                    'is_active' => true,
                ])->id;
            }
        }

        return self::$categoryIds;
    }
}

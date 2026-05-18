<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\InventoryCategory;
use App\Repositories\BookRepository;
use Database\Factories\BookFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookPerformanceFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_factory_generates_valid_isbn13(): void
    {
        $isbn = BookFactory::isbn13(123456789);

        $this->assertSame(13, strlen($isbn));
        $this->assertTrue($this->isValidIsbn13($isbn));
    }

    public function test_repository_uses_cursor_pagination_and_eager_loading(): void
    {
        $category = InventoryCategory::create(['name' => 'Books', 'slug' => 'books', 'is_active' => true]);
        Book::factory()->count(3)->create(['category_id' => $category->id, 'is_active' => true]);

        $result = app(BookRepository::class)->catalog(['category_id' => $category->id], 2);

        $this->assertCount(2, $result->items());
        $this->assertTrue($result->items()[0]->relationLoaded('category'));
    }

    public function test_book_api_supports_isbn_lookup(): void
    {
        $category = InventoryCategory::create(['name' => 'Books', 'slug' => 'books', 'is_active' => true]);
        $book = Book::factory()->create(['category_id' => $category->id, 'is_active' => true]);

        $this->getJson('/api/books/' . $book->isbn13)
            ->assertOk()
            ->assertJsonPath('data.isbn13', $book->isbn13);
    }

    private function isValidIsbn13(string $isbn): bool
    {
        $sum = 0;

        for ($i = 0; $i < 12; $i++) {
            $sum += (int) $isbn[$i] * ($i % 2 === 0 ? 1 : 3);
        }

        return (10 - ($sum % 10)) % 10 === (int) $isbn[12];
    }
}

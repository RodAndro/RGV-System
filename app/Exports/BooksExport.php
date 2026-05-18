<?php

namespace App\Exports;

use App\Models\Book;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class BooksExport implements FromQuery, WithHeadings, WithMapping, WithChunkReading
{
    public function query()
    {
        return Book::query()
            ->select(['isbn13', 'title', 'category_id', 'author', 'format', 'price', 'stock', 'sales_count', 'rating'])
            ->with('category:id,name')
            ->where('is_active', true)
            ->orderBy('id');
    }

    public function headings(): array
    {
        return ['ISBN-13', 'Title', 'Category', 'Author', 'Format', 'Price', 'Stock', 'Sales', 'Rating'];
    }

    public function map($book): array
    {
        return [
            $book->isbn13,
            $book->title,
            $book->category?->name,
            $book->author,
            $book->format,
            $book->price,
            $book->stock,
            $book->sales_count,
            $book->rating,
        ];
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}

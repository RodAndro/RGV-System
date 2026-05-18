<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookResource;
use App\Repositories\BookRepository;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function __construct(private readonly BookRepository $books)
    {
    }

    public function index(Request $request)
    {
        return BookResource::collection($this->books->catalog($request->only(['category_id', 'min_price', 'max_price', 'format']), (int) $request->input('per_page', 50)));
    }

    public function show(string $isbn)
    {
        $book = $this->books->findByIsbn($isbn);
        abort_if(! $book, 404);

        return new BookResource($book);
    }

    public function search(Request $request)
    {
        $request->validate(['q' => ['required', 'string', 'min:2']]);

        return BookResource::collection($this->books->search($request->q, (int) $request->input('per_page', 25)));
    }
}

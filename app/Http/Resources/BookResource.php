<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'isbn13' => $this->isbn13,
            'title' => $this->title,
            'author' => $this->author,
            'format' => $this->format,
            'price' => $this->price,
            'stock' => $this->stock,
            'rating' => $this->rating,
            'category' => new InventoryCategoryResource($this->whenLoaded('category')),
        ];
    }
}

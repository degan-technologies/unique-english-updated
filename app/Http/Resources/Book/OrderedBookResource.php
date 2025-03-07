<?php

namespace App\Http\Resources\Book;

use App\Http\Resources\userResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderedBookResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'enrolled_at' => $this->enrolled_at,
            'not_deleted' => $this->not_deleted,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'user' => new userResource($this->whenLoaded('user')),
            'book' => new BookResource($this->whenLoaded('books')),
        ];
    }
}

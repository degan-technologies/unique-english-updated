<?php

namespace App\Http\Resources\Transaction;

use App\Http\Resources\Book\BookResource;
use App\Http\Resources\Course\CourseResource;
use App\Http\Resources\Live\LiveSessionResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MyEnrollmentsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'course' => new CourseResource($this->course),
            'book' => new BookResource($this->book),
            'live' => new LiveSessionResource($this->live),
        ];
    }
}

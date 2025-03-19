<?php

namespace App\Http\Resources\Transaction;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseTransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'course_name' => $this->course_name,
            'price' => $this->price,
            'discount' => $this->discount,

            'thumbnail_url' => $this->thumbnail_url
                ? Storage::disk('public')->url($this->thumbnail_url)
                : 'no-thumbnail_url.png',
        ];
    }
}

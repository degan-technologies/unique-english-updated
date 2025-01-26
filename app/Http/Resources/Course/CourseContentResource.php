<?php

namespace App\Http\Resources\Course;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class CourseContentResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'course_id' => $this->course_id,
            'title' => $this->title,
            'description' => $this->description,
            'content_type' => $this->content_type,
            'hour' => $this->hour,
            'status' => $this->status,
            'sequence' => $this->sequence,
            'note' => $this->note,

            'content_url' => $this->content_url
                ? Storage::disk('public')->url($this->content_url)
                : 'no-content_url.png',
            'thumbnail_url' => $this->thumbnail_url
                ? Storage::disk('public')->url($this->thumbnail_url)
                : 'no-thumbnail_url.png',
        ];
    }
}

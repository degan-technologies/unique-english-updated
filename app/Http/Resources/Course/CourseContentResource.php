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
            'module_id' => $this->course_module_id,
            'course_id' => $this->course_id,          
            'title' => $this->title,
            'description' => $this->description,
            'content_type' => $this->content_type,
            'hour' => $this->hour,
            'status' => $this->status,
            'sequence' => $this->sequence,
            'note' => $this->note,
            'course_content_url' => $this->content_url 
            ? url('/api/coursecontent/stream/video/' . basename($this->content_url))
            : 'no-intro_video.png',
            'thumbnail_url' => $this->thumbnail_url
                ? Storage::disk('public')->url($this->thumbnail_url)
                : 'no-thumbnail_url.png',
        ];
    }
    
}

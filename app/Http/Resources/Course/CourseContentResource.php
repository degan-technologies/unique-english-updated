<?php

namespace App\Http\Resources\Course;

use App\Models\Course\CourseContent;
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

        $duration = CourseContent::timeToSeconds($this->hour);

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'module_id' => $this->course_module_id,
            'course_id' => $this->course_id,          
            'title' => $this->title,
            'sequence' => $this->sequence,
            'content_type' => $this->content_type,
            'note' => $this->note,

            'hour' => $this->hour,
            'duration' =>$duration,
            'description' => $this->description,
            'content_type' => $this->content_type,
            'status' => $this->status,
            'note' => $this->note,
            'content_url' => $this->content_url
                ? Storage::disk('public')->url($this->content_url)
                : 'no-content_url.png',

            'course_content_url' => $this->content_url 
            ? url('/api/coursecontent/stream/video/' . basename($this->content_url))
            : 'no-intro_video.png',

            'thumbnail_url' => $this->thumbnail_url
                ? Storage::disk('public')->url($this->thumbnail_url)
                : 'no-thumbnail_url.png',

            'courseContentProgress' => new CourseContentProgressResource($this->courseContentProgress, $duration),
        ]; 
    }
}

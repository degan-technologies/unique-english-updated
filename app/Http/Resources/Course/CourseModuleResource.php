<?php

namespace App\Http\Resources\Course;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseModuleResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        return [
            'id'=>$this->id,
            'title' => $this->title,
            'sequence' => $this->sequence,
            'description' => $this->description,
            'slug' => $this->slug,
            'course_id' => $this->course_id,      
            'courseContents' => CourseContentResource::collection($this->courseContents->sortBy('sequence')),
        ];
    }
}

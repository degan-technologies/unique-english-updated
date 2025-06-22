<?php

namespace App\Http\Resources\StudentResources\StdCourse;
 
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource; 

class StdCourseContentResource extends JsonResource {
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
            'sequence' => $this->sequence,
            'note' => $this->note,
            'content_type' => $this->content_type, 
        ];
    }
    
}

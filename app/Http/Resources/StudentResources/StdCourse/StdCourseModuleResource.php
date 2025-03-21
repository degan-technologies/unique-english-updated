<?php

namespace App\Http\Resources\StudentResources\StdCourse; 

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource; 

class StdCourseModuleResource extends JsonResource {
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
            'QMetaDatas' => StdQMetaDataResource::collection($this->QMetaDatas),
            'courseContents' => StdCourseContentResource::collection($this->courseContents->sortBy('sequence')),
        ];
    }
}

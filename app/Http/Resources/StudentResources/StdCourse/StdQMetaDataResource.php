<?php

namespace App\Http\Resources\StudentResources\StdCourse;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StdQMetaDataResource extends JsonResource {
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'user_id' => $this->user_id,
            'course_id' => $this->course_id, 
            'course_module_id' => $this->course_module_id,
        ];
    }
}

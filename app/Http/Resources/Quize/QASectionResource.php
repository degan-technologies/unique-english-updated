<?php

namespace App\Http\Resources\Quize;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QASectionResource extends JsonResource {
      
    public function toArray(Request $request): array {
        return [
            'id' => $this->id,
            'question' => $this->question,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
            'user' => new UserResource($this->user),
            'course' => new CourseResource($this->course),
            'replies' => QASectionResource::collection($this->replies),
            'parent_section' => new QASectionResource($this->parentSection),
        ];
    }
}

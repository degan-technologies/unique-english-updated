<?php

namespace App\Http\Resources\Quiz;

use App\Http\Resources\Course\CourseResource;
use App\Http\Resources\userResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QASectionResource extends JsonResource {
      
    public function toArray(Request $request): array {
        return [
            'id' => $this->id,
            'question' => $this->question,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at, 
            'replies' => QASectionResource::collection($this->replies), 
        ];
    }
}

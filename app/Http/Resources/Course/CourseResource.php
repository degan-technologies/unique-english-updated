<?php

namespace App\Http\Resources\Course;

use App\Http\Resources\userResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        return [
            'id' => $this->id,
            'course_name' => $this->course_name,
            'overview' => $this->overview,
            'tag' => $this->tag,
            'skill_level' => $this->skill_level,
            'price' => $this->price,
            'discount' => $this->discount,
            'credit_hour' => $this->credit_hour,
            'user' => new userResource($this->user)
        ];
    }
}

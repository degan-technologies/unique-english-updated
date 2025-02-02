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
            'slug' => $this->slug,
            'course_name' => $this->course_name,
            'overview' => $this->overview,
            'tag' => $this->tag,
            'skill_level' =>$this->skillLevel($this->skill_level),
            'language' => $this->language,
            'price' => $this->price,
            'discount' => $this->discount,
            'credit_hour' => $this->credit_hour,
            'user' => new userResource($this->user),
            'thumbnail_url' => $this->thumbnail_url,
            'courseModules' => CourseModuleResource::collection($this->courseModules),
            'courseContents' => $this->courseModules ? null : CourseContentResource::collection($this->courseContents),
        ];
    }

    public function skillLevel($leve) {
        switch($leve){
            case BIGINNER;
                return 'Bignner';
            case INTERMIDIATE;
                return 'Intermidiate';
            case ADVANCE;
                return 'Advance';
            case FULL_PACKAGE;
                return 'Full Package';
            default :
            return 'not assigned';
        }
    }
}

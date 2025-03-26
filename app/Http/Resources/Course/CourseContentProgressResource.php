<?php

namespace App\Http\Resources\Course;

use App\Models\Course\CourseContent;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseContentProgressResource extends JsonResource {

    protected $parentData;

    public function __construct($resource, $parentData = null) {
        parent::__construct($resource);
        $this->parentData = $parentData;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
 
        return [
            'id' => $this->id,
            'progress' => $this->progress,
            'max_progress' => $this->progressInPercentage($this->parentData, $this->progress),
            'vedeo_progress' => $this->vedeo_progress,
            'max_vedeo_progress' => $this->progressInPercentage($this->parentData, $this->vedeo_progress),
            'course_content_id' => $this->course_content_id,
        ];
    }

    public function progressInPercentage($durattion, $progress): int {
        $progress = CourseContent::timeToSeconds($progress);

        if ($durattion == 0) {
            return 0;
        }

        return round(($progress / $durattion) * 100);
    }

   
}

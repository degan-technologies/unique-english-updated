<?php

namespace App\Http\Resources\Course;

use App\Models\Course\Course;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class MyCourseResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'course_name' => $this->course_name,
            'overview' => $this->overview,
            'tag' => $this->tag, 

            'intro_video_url' => $this->intro_video
                ? url('/api/courses/stream/video/' . basename($this->intro_video))
                : 'no-intro_video.png',
            'thumbnail_url' => $this->thumbnail_url
                ? Storage::disk('public')->url($this->thumbnail_url)
                : 'no-thumbnail_url.png',

            'isMyCourse' => Course::checkEligibility($this->id),
            'progress' => Course::getCourseProgress($this->slug)['overAllPogress'],
        ];
    }
}

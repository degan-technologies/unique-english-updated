<?php

namespace App\Http\Resources\Course;

use App\Models\Course\Course;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Services\CloudFrontService;

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
                ? CloudFrontService::signedUrl($this->intro_video, now()->addMinutes(60))
                : 'no-intro_video.png',

            // HLS master playlist — plain CloudFront URL; cookies sent separately
            'intro_video_hls_url' => $this->hls_path
                ? CloudFrontService::hlsUrl($this->hls_path)
                : 'no-intro_video.png',

            'thumbnail_url' => $this->thumbnail_url
                ? CloudFrontService::signedUrl($this->thumbnail_url, now()->addMinutes(120))
                : 'no-thumbnail_url.png',

            'isMyCourse' => Course::checkEligibility($this->id),
            'progress' => Course::getCourseProgress($this->slug)['overAllPogress'],
        ];
    }
}

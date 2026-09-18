<?php

namespace App\Http\Resources\Course;

use App\Helper\TokenGenerator;
use App\Models\Course\CourseContent;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Services\CloudFrontService;
use Illuminate\Support\Facades\Auth;

class CourseContentResource extends JsonResource
{
    const DEFAULT_THUMBNAIL = 'no-thumbnail_url.png';
    const DEFAULT_CONTENT = 'no-content_url.png';

    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $duration = CourseContent::timeToSeconds($this->hour);

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'module_id' => $this->course_module_id,
            'course_id' => $this->course_id,
            'title' => $this->title,
            'sequence' => $this->sequence,
            'content_type' => $this->content_type,
            'video_optimized' => $this->video_optimized ? true : false,
            'note' => $this->note,
            'created_at' => $this->created_at?->format('l, F j Y'),
            'hour' => $this->formatHour($this->hour),
            'duration' => $duration,
            'status' => $this->status,
            // Lesson video URL — signed URL for MP4 fallback; HLS handled via cookies
            'course_content_url' => $this->content_url
                ? CloudFrontService::signedUrl($this->content_url, now()->addMinutes(60))
                : self::DEFAULT_CONTENT,

            // Lesson thumbnail — signed URL
            'thumbnail_url' => $this->thumbnail_url
                ? CloudFrontService::signedUrl($this->thumbnail_url, now()->addMinutes(120))
                : self::DEFAULT_THUMBNAIL,
            'courseContentProgress' => new CourseContentProgressResource($this->courseContentProgress, $duration),
        ];
    }

    protected function formatHour(?string $hour): ?string
    {
        return $hour && str_starts_with($hour, '00:')
            ? substr($hour, 3)
            : $hour;
    } 
}

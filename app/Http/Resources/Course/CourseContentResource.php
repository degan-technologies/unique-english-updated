<?php

namespace App\Http\Resources\Course;

use App\Helper\TokenGenerator;
use App\Models\Course\CourseContent;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
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
            'course_content_url' => $this->course_content_url
                ? Storage::disk('s3')->temporaryUrl($this->course_content_url, now()->addMinutes(30))
                : self::DEFAULT_CONTENT,

            'thumbnail_url' => $this->thumbnail_url
                ? Storage::disk('s3')->temporaryUrl($this->thumbnail_url,  now()->addMinutes(30))
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

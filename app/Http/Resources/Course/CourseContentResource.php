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
            'course_content_url' => $this->getContentUrl($this->content_type, $this->content_url),
            'courseContentProgress' => new CourseContentProgressResource($this->courseContentProgress, $duration),
        ];
    }

    protected function formatHour(?string $hour): ?string
    {
        return $hour && str_starts_with($hour, '00:')
            ? substr($hour, 3)
            : $hour;
    }

    protected function getThumbnailUrl(): string
    {
        return $this->thumbnail_url
            ? Storage::disk('public')->url($this->thumbnail_url)
            : self::DEFAULT_THUMBNAIL;
    }

    protected function getContentUrl(?string $contentType, ?string $content): string
    {
        if (empty($contentType) || empty($content)) {
            return self::DEFAULT_CONTENT;
        }

        try {
            $filename = basename($content);
            $userId = Auth::id();

            switch ($contentType) {
                case VIDEO:
                    return TokenGenerator::generateSecureUrl('stream.video', $filename);

                case PDF:
                    return TokenGenerator::generateSecurePdfUrl('stream.pdf', $filename, $userId);
                case IMAGE:
                    return Storage::disk('public')->url($content);
                default:
                    return self::DEFAULT_CONTENT;
            }
        } catch (\Exception $e) {
            report($e);
            return self::DEFAULT_CONTENT;
        }
    } 
}

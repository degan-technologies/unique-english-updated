<?php

namespace App\Http\Resources\Course; 

use App\Models\Course\Course;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use App\Models\Transaction\Transaction; 
 class CourseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'course_name' => $this->course_name,
            'overview' => $this->overview,
            'tag' => $this->tag,
            'skill_level' => $this->skillLevel($this->skill_level),
            'skill_level_id' => $this->skill_level,
            'language' => $this->language,
            'price' => $this->price,
            'discount' => $this->discount,
            'credit_hour' => $this->credit_hour,
            'created_at' => $this->created_at,
            'status' => $this->status,
            'total_enroll' => $this->totalEnroll($this->id),
            'revenue' => $this->totalRevenue($this->id),
            'video_optimized' => $this->video_optimized ? true : false,
 
            'intro_video_url' => $this->intro_video
                ? Storage::disk('s3')->temporaryUrl($this->intro_video, now()->addMinutes(60))
                : 'no-intro_video.png',

            'intro_video_hls_url' => $this->hls_path
                ? Storage::disk('s3')->temporaryUrl($this->hls_path, now()->addMinutes(60))
                : 'no-intro_video.png',

            'thumbnail_url' => $this->thumbnail_url
                ? Storage::disk('s3')->temporaryUrl($this->thumbnail_url,  now()->addMinutes(30))
                : 'no-thumbnail_url.png',
            'courseModules' => $this->whenLoaded('courseModules', function () {
                return CourseModuleResource::collection($this->courseModules->sortBy('sequence'));
            }),

            'isMyCourse' => Course::checkEligibility($this->id),
        ];
    }

    public function skillLevel($leve)
    {
        switch ($leve) {
            case BIGINNER;
                return 'Beginner';
            case INTERMIDIATE;
                return 'Intermediate';
            case ADVANCE;
                return 'Advance';
            case FULL_PACKAGE;
                return 'Full Package';
            default:
                return 'not assigned';
        }
    }

    public function totalEnroll($id)
    {

        if (isset($this->total_enroll)) {
            return $this->total_enroll === 0 ? 'not selled' : $this->total_enroll;
        }

        $countTotalEnroll = Transaction::query()
            ->where('course_id', $id)
            ->where('status', 'success')
            ->count();

        return $countTotalEnroll === 0 ?  'not selled' : $countTotalEnroll;
    }

    public function totalRevenue($id)
    {
        if (isset($this->total_revenue)) {
            return $this->total_revenue === 0 ? 'not selled' : $this->total_revenue;
        }

        $countRevenue = Transaction::query()
            ->where('course_id', $id)
            ->where('status', 'success')
            ->sum('amount');

        return $countRevenue === 0 ?  'not selled' : $countRevenue;
    }
}

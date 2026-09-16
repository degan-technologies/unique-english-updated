<?php

namespace App\Http\Resources\StudentResources\StdCourse;

use App\Http\Resources\Comment\FeedBackResource; 
use App\Http\Resources\userResource;
use App\Models\Comment\FeedBack;
use App\Models\Course\Course;
use App\Models\Transaction\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;



class StdCourseResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        $review = FeedBack::reviewRate($this->feedbacks);

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
            'user' => new userResource($this->user),

            'intro_video_url' => $this->intro_video
                ? Storage::disk('s3')->temporaryUrl($this->intro_video, now()->addMinutes(60))
                : 'no-intro_video.png',

            'intro_video_hls_url' => $this->hls_path
                ? Storage::disk('s3')->temporaryUrl($this->hls_path, now()->addMinutes(60))
                : 'no-intro_video.png',
 
            'averageRating' => $review['averageRating'],
            'starDistribution' => $review['starDistribution'],

            'thumbnail_url' => $this->thumbnail_url
                ? Storage::disk('s3')->temporaryUrl($this->thumbnail_url,  now()->addMinutes(30))
                : 'no-thumbnail_url.png',
            'courseModules' => $this->whenLoaded('courseModules', function () {
                return StdCourseModuleResource::collection($this->courseModules->sortBy('sequence'));
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
}

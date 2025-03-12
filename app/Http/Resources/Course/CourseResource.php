<?php

namespace App\Http\Resources\Course;

use App\Http\Resources\Comment\FeedBackResource;
use App\Http\Resources\Transaction\TransactionResource;
use App\Http\Resources\userResource;
use App\Models\Comment\FeedBack;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;



class CourseResource extends JsonResource {
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
            // Use the streaming endpoint for the intro video
       'intro_video_url' => $this->intro_video 
            ? url('/api/courses/stream/video/' . basename($this->intro_video))
            : 'no-intro_video.png',





            'thumbnail_url' => $this->thumbnail_url
                ? Storage::disk('public')->url($this->thumbnail_url)
                : 'no-thumbnail_url.png',
            'courseModules' => CourseModuleResource::collection($this->courseModules->sortBy('sequence')),
        ];
    }

    public function skillLevel($leve) {
        switch($leve){
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

<?php

namespace App\Http\Resources\StudentResources\StdCourse;

use App\Http\Resources\Comment\FeedBackResource; 
use App\Http\Resources\userResource;
use App\Models\Comment\FeedBack;
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
                ? url('/api/courses/stream/video/' . basename($this->intro_video))
                : 'no-intro_video.png',

            'feedBacks' => FeedBackResource::collection($this->feedbacks),
            'averageRating' => $review['averageRating'],
            'starDistribution' => $review['starDistribution'],


            'thumbnail_url' => $this->thumbnail_url
                ? Storage::disk('public')->url($this->thumbnail_url)
                : 'no-thumbnail_url.png',
            'courseModules' => StdCourseContentResource::collection($this->courseModules->sortBy('sequence')),

            'isMyCourse' => $this->checkEligibility(),
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

    public function checkEligibility()
    {
        $user = Auth::guard('api')->user();

        if (!$user) {
            return false;
        }

        $myTransactions = Transaction::query()
            ->where('customer_id', $user->id)
            ->where('course_id', $this->id)
            ->where('status', TRANSACTION_SUCCESS)
            ->first();


        if ($myTransactions) {
            return true;
        }

        return false;
    }
}

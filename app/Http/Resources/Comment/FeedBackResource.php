<?php

namespace App\Http\Resources\Comment;

use App\Http\Resources\UserDataResource;
use App\Http\Resources\userResource;
use App\Models\Comment\FeedBack;
use App\Models\Comment\FeedbackUserInteraction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class FeedBackResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'rating'    => $this->rate, 
            'comment'   => $this->comment,
            'timestamp' => $this->created_at->toDateTimeString(),
            'likes'     => $this->getInteraction()['like'],
            'dislikes'  => $this->getInteraction()['dislike'],
            'reports'   => $this->reports,
            'user'      => new UserDataResource($this->user),
            'myFeedback' => $this->myFeedback(),
        ];
    }


    public function getInteraction(){
        $reaction = FeedbackUserInteraction::query()
            ->where('feed_back_id', $this->id)
            ->get();

        $countLike = $reaction ->where('favorite', 'liked')->count();
        $countDislike = $reaction ->where('favorite', 'disliked')->count();

        return [
            'like' => $countLike,
            'dislike' => $countDislike,
        ];
    }

    public function myFeedback() {
        $user = Auth::guard('api')->user(); 

        if (!$user) {
            return false;
        }

        if ($user->id === $this->user_id) {
            return true;
        }

        if ($user->id === $this->instractor_id) {
            return true;
        }

        return false;
    }
}

<?php

namespace App\Http\Resources\Comment;

use App\Http\Resources\userResource;
use App\Models\Comment\FeedbackUserInteraction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'rating'    => $this->rate, // mapping "rate" to "rating"
            'comment'   => $this->comment,
            'timestamp' => $this->created_at->toDateTimeString(),
            'likes'     => $this->getInteraction()['like'],
            'dislikes'  => $this->getInteraction()['dislike'],
            'reports'   => $this->reports,
            'user'      => new userResource($this->user),
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
}

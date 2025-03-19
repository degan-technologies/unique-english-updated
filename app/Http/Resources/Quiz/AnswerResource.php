<?php

namespace App\Http\Resources\Quiz;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnswerResource extends JsonResource {

    public function toArray($request) {
        return [
            'id'         => $this->id,
            'answer'     => $this->answer,
            'user'       => new UserResource($this->user), // Include full user details
            'question_id'=> $this->question_id,
            'course_id'  => $this->course_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

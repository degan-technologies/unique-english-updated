<?php

namespace App\Http\Resources\Quiz;

use App\Http\Resources\UserDataResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class AnswerResource extends JsonResource {

    public function toArray($request) {

        $user = Auth::user();

        return [
            'id'         => $this->id,
            'answer'     => $this->answer,
            'user'       => new UserDataResource($this->user), 
            'question_id'=> $this->question_id,
            'course_id'  => $this->course_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'editable'   => $user->id === $this->user_id,
        ];
    }
}

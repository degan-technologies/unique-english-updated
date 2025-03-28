<?php

namespace App\Http\Resources\Quiz;

use App\Http\Resources\Course\CourseResource;
use App\Http\Resources\UserDataResource;
use App\Http\Resources\userResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class QASectionResource extends JsonResource {
      
    public function toArray(Request $request): array {
        $user = Auth::user();
        return [
            'id' => $this->id,
            'question' => $this->question,
            'course_id' => $this->course_id,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at, 
            'user'      => new UserDataResource($this->user),
            'editable'   => $user->id === $this->user_id,
            'answers' => AnswerResource::collection($this->answers),
        ];
    }
}

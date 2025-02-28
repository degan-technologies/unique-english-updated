<?php

namespace App\Http\Resources\Quiz;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QMetaDataResource extends JsonResource {
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'user_id' => $this->user_id,
            'course_id' => $this->course_id,
            'deleted_at' => $this->deleted_at,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
            'instruction' => $this->instraction,
            'question_type' => $this->question_type,
            'course_content_id' => $this->course_content_id,

            'questions' => QuizResource::collection($this->quizzes),
        ];
    }
}

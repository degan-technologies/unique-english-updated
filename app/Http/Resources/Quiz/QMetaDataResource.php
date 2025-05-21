<?php

namespace App\Http\Resources\Quiz;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use phpDocumentor\Reflection\PseudoTypes\True_;

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
            'instruction' => $this->instruction,
            'question_type' => $this->question_type,
            'course_module_id' => $this->course_module_id,

            'questions' => QuizResource::collection($this->quizzes),
            'is_completed' => $this->checkIfCompleted($this->results),
            'best_score' => $this->getBestScore($this->results),
        ];
    }

    public function checkIfCompleted($results)
    {
        if (!$results || $results->isEmpty()) {
            return false;
        }
 
        return $results->contains(fn ($result) => $result->result > 70);
    }

    public function getBestScore($results)
    {
        if (!$results || $results->isEmpty()) {
            return 'not taken';
        }

        return $results->max('result');
    }

}

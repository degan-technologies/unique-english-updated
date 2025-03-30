<?php

namespace App\Http\Resources\Quiz;

use App\Models\Quiz\QuizAnswer;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class QuizResource extends JsonResource {
  
    public function toArray($request) {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'answer' => $this->answer,
            'choice' => $this->choice,
            'question' => $this->question,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'question_type' => $this->question_type,
            'exam_id' => $this->q_meta_data_id,
            'hint' => $this->hint,
            'checkAnswer' => $this->quizAnswers ? $this->checkAnswer() : false,
        ];
    }

    public function checkAnswer() { 
        $user = Auth::user(); 

        $check = QuizAnswer::query()
            ->where('user_id', $user->id)
            ->where('quiz_id', $this->id)
            ->where('answer', CORRECT)
            ->first();

        if ($check) {
            return true;
        }

        return false;        
    }
}

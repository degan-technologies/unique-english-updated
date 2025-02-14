<?php

namespace App\Http\Resources\Quize;

use Illuminate\Http\Resources\Json\JsonResource;

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
            'q_meta_data_id' => $this->q_meta_data_id,

        ];
    }
}

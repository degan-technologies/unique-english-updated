<?php

namespace App\Http\Resources\Quiz;

use App\Http\Resources\userResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResultResource extends JsonResource {
    public function toArray(Request $request){
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'result' => $this->result,
            'user' => new userResource($this->user),
            'quiz' => new QuizResource($this->quiz),
            'quiz_id'=> $this->q_meta_data_id,
            'course_module_id'=>$this->course_module_id,
            'course_id'=> $this->course_id,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}

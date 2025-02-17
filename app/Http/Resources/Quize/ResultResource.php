<?php

namespace App\Http\Resources\Quize;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResultResource extends JsonResource {
    public function toArray(Request $request){
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'result' => $this->result,
            'user' => new UserResource($this->user),
            'quiz' => new QuizResource($this->quiz),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}

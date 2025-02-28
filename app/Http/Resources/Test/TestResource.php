<?php

namespace App\Http\Resources\Test;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'question' => $this->question,
            'choices' => $this->choices,
            'answer' => $this->answer,
            'score' => $this->score,
            'level' => $this->level,
        ];
    }
}

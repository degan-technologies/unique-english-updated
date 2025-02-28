<?php

namespace App\Http\Resources\Schedule;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScheduleResource extends JsonResource {
    public function toArray(Request $request): array {
        return [
            'id' => $this->id,
            'day' => $this->day,
            'time' => $this->time,
            'user_id' => $this->user_id,
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}

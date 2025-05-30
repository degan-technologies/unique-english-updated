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
            'status' => $this->status,
            'schedule_time' => $this->schedule_time,
            'created_at' => $this->created_at->toDateTimeString(),
            'color' => $this->sessionStatusClass($this->status),
            'room_name' => $this->room_name,

            'class_name' => $this->peredicTable?->liveRoom?->class_name,
            'instructor_name' => $this->getInstructorName(),
            'student_name' => $this->student?->first_name  . ' ' .  $this->student?->middle_name,          
        ];
    }

    public function sessionStatusClass($status) {

        if ($status === LIVE) return "bg-green-500 text-white";
        if ($status === CANCELLED) return "bg-red-500 text-white";
        if ($status === COMPLETED) return "bg-blue-500 text-white";

        return "bg-yellow-500 text-white";
    }

    public function getInstructorName() {
        if($this->peredicTable){
            return $this->peredicTable?->liveRoom?->instructor?->first_name . ' ' . $this->peredicTable?->liveRoom?->instructor?->middle_name;
        } else {
            return  $this->student?->privateRoom->instructor?->first_name . ' ' . $this->peredicTable?->privateRoom?->instructor?->middle_name;
        } 
    } 
}

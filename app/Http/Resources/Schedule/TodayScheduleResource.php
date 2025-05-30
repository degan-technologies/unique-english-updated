<?php

namespace App\Http\Resources\Schedule;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TodayScheduleResource extends JsonResource {
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
            'info' => $this->getInfo(),                       
        ];
    }

     public function getInfo() {
        if($this->peredicTable){
            return [
                'instructor_name'=> $this->peredicTable?->liveRoom?->instructor?->first_name . ' ' . $this->peredicTable?->liveRoom?->instructor?->middle_name,
                'class_or_student_name' => $this->peredicTable?->liveRoom?->class_name, 
                'type' => 'Group',
            ];
        } else {
            return  [
                'instructor_name'=>$this->student?->privateRoom->instructor?->first_name . ' ' . $this->student?->privateRoom->instructor?->middle_name,
                'class_or_student_name' => $this->student?->first_name  . ' ' .  $this->student?->middle_name,
                'type' => 'Private',
            ];
        } 
    } 

    public function sessionStatusClass($status) {

    if ($status === LIVE) return "bg-green-500 text-white";
    if ($status === CANCELLED) return "bg-red-500 text-white";
    if ($status === COMPLETED) return "bg-blue-500 text-white";

    return "bg-yellow-500 text-white";
}
}

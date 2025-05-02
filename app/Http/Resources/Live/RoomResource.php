<?php

namespace App\Http\Resources\Live;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoomResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'class_name' => $this->class_name,
            'participants' => $this->groupRooms->count(),
            'instructor_name' => $this->user->first_name ?? 'Not Assigned',
            'middle_name' => $this->user->middle_name,
        ];
    }
}

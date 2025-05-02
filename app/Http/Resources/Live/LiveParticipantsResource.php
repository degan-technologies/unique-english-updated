<?php

namespace App\Http\Resources\Live;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class LiveParticipantsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return  [
            'id'          => $this->id,
            'gender'      => $this->gender,
            'email'       => $this->email,
            'first_name'   => $this->first_name,
            'middle_name'       => $this->middle_name,
            'phone'       => $this->phone,
            'profile' => $this->profile
                ? Storage::disk('public')->url($this->profile)
                : 'images/no-profile.png',     
                
            'class_name' => $this->groupRoom?->liveRoom?->class_name,
        ];
    }
}

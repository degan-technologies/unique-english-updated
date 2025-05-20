<?php

namespace App\Http\Resources\Live;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class PrivateLiveParticipantResource extends JsonResource
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
                
            'instructor_name' => $this->privateRoom?->instructor ? 
                ($this->privateRoom->instructor->first_name . ' ' . $this->privateRoom->instructor->middle_name) : null,
        ];
    }
}

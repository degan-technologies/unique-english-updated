<?php

namespace App\Http\Resources\Live;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Services\CloudFrontService;

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
                ? CloudFrontService::signedUrl($this->profile, now()->addMinutes(30))
                : 'images/no-profile.png',     
            
            'class_name' => $this->groupRoom?->liveRoom?->class_name,
            'instructor_name' => $this->privateRoom?->instructor ? 
                ($this->privateRoom->instructor->first_name . ' ' . $this->privateRoom->instructor->middle_name) : null,
        ];
    }
}

<?php

namespace App\Http\Resources\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
class CurrentUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        return [
            'id' => $this->id,
            'phone' => $this->phone,
            'email' => $this->email,
            'gender' => $this->gender,
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,

            'profile' => $this->profile
                ? Storage::disk('public')->url($this->profile)
                : 'no-profile.png',
            'bg_image' => $this->bg_image
                ? Storage::disk('public')->url($this->bg_image)
                : 'no-bg_image.png',
        ];
    }
}

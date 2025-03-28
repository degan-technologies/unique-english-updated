<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class UserDataResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        return [
            'id'          => $this->id,
            'slug'        => $this->slug,
            'full_name'   => $this->first_name . ' ' . $this->middle_name,
            'profile' => $this->profile
                ? Storage::disk('public')->url($this->profile)
                : 'no-profile.png',
        ];;
    }
}

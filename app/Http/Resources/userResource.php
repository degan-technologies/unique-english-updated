<?php

namespace App\Http\Resources;

use App\Http\Resources\Transaction\TransactionResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class UserResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        return [
            'id'          => $this->id,
            'slug'        => $this->slug,
            'gender'      => $this->gender,
            'email'       => $this->email,
            'first_name'  => $this->first_name,
            'middle_name' => $this->middle_name,
            'last_name'   => $this->last_name,
            'full_name'   => $this->full_name,
            'phone'       => $this->phone, 
            'bg_image'    => $this->bg_image,
            'role'        => $this->role == 2 ? 'INSTRUCTOR_ROLE' : 'STUDENT_ROLE', 
            'joinDate'    => $this->created_at ? $this->created_at->format('Y-m-d') : null,
            'progress'    => $this->progress ?? 0,
            'myCourse'    => TransactionResource::collection($this->transaction),
            'temp_password' => $this->temp_password ?? null,
            'otp' => $this->otp ?? null,
            'otp_expires_at' => $this->otp_expires_at ? $this->otp_expires_at->format('Y-m-d H:i:s') : null,
            'otp_attempts' => $this->otp_attempts ?? 0,
            
            'profile' => $this->profile
                ? Storage::disk('public')->url($this->profile)
                : 'images/no-profile.png',
        ];
    }
}

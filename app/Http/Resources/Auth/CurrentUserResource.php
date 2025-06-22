<?php

namespace App\Http\Resources\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
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
            'is_verified' => $this->email_verified_at ? true : false,
            'profile' => $this->profile
                ? Storage::disk('public')->url($this->profile)
                : 'images/no-profile.png',

            'bg_image' => $this->bg_image
                ? Storage::disk('public')->url($this->bg_image)
                : 'images/background_gugut.jpg',

            'role'=>$this->getRole(),
        ];
    }

    public function getRole() {

        /**
         * @var user 
         */
        $user = Auth::user();

        $student = $user->student()->exists();

        if($student) {
            return 'student';
        }

        $instructor = $user->instructor()->exists();

        if ($instructor) {
            return 'instructor';
        }

        $systemAdmin = $user->systemAdmin()->exists();

        if ($systemAdmin) {
            return 'systemAdmin';
        }
    }
}

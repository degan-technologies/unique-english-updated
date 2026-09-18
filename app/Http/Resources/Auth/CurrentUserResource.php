<?php

namespace App\Http\Resources\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use App\Services\CloudFrontService;
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
                ? CloudFrontService::signedUrl($this->profile, now()->addMinutes(60))
                : 'images/no-profile.png',

            'bg_image' => $this->bg_image
                ? CloudFrontService::signedUrl($this->bg_image, now()->addMinutes(60))
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

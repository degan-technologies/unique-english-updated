<?php

namespace App\Http\Resources\Transaction;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\User;
use App\Services\CloudFrontService;

class CustomerInfoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'gender'      => $this->gender,
            'email'       => $this->email,
            'first_name'   => $this->first_name,
            'middle_name'       => $this->middle_name,
            'temp_password' => $this->temp_password ?? null,
            'status' => $this->user_banned_at ? 'Blocked' : 'Active',
            'phone'       => $this->phone,
            'profile' => $this->profile
                ? CloudFrontService::signedUrl($this->profile, now()->addMinutes(30))
                : 'images/no-profile.png',

            'role'=>$this->getRole(),
            'engagement' => $this->transactions && $this->transactions->count() > 0 ? 'yes' : 'no',
            'joinDate' => $this->created_at->diffForHumans(),
        ];
    }

    public function getRole() {

        /**
         * @var user 
         */
        $user = User::find($this->id);
        if (!$user) {
            return null;
        }

        $student = $user->student()->exists();

        if($student) {
            return [
                'name' => 'student',
                'text-color' => '#065f46',
                'bg-color' => '#ecfccb',
            ];
        }

        $instructor = $user->instructor()->exists();

        if ($instructor) {
            return [
                'name' => 'instructor',
                'text-color' => '#7e22ce',
                'bg-color' => '#e0e7ff',
            ];
        }

        $systemAdmin = $user->systemAdmin()->exists();

        if ($systemAdmin) {
            return [
                'name' => 'system admin',
                'text-color' => '#dc2626',
                'bg-color' => '#fee2e2',
            ];
        }
    }
}

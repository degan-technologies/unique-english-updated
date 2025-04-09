<?php

namespace App\Http\Resources\Transaction;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\User;


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
            'phone'       => $this->phone,
            'profile' => $this->profile
                ? Storage::disk('public')->url($this->profile)
                : 'no-profile.png',

            'role'=>$this->getRole(),
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

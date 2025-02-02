<?php

namespace App\Http\Resources\Live;

use App\Http\Resources\userResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VirtualClassEnrollmentResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'end_date' => $this->end_date,
            'price_plan' => $this->price_plan,
            'enrolled_at' => $this->enrolled_at,
            'user' => new userResource($this->user),
            'remaining_date' => $this->remaining_date,
            'instructor' => new UserResource($this->instructor),
        ];
    }
}

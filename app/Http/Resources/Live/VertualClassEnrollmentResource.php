<?php

namespace App\Http\Resources\Live;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VertualClassEnrollmentResource extends JsonResource
{
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
            'user' => new UserResource($this->user),
            'remaining_date' => $this->remaining_date,
            'instructor' => new UserResource($this->instructor),
        ];
    }
}

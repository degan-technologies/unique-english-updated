<?php

namespace App\Http\Resources\Transaction;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'full_name'   => $this->full_name,
            'phone'       => $this->phone,
            'profile'     => $this->profile,
        ];
    }
}

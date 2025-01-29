<?php

namespace App\Http\Resources\Live;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ParticipantResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array{
        return [
            'id' => $this->id,
            'status' => $this->status,
            'joined_at' => $this->joined_at,
            'user' => new UserResource($this->user),
            'live_session' => new LiveSessionResource($this->liveSession),
        ];
    }
}

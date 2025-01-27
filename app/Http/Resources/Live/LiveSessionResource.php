<?php

namespace App\Http\Resources\Live;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LiveSessionResource extends JsonResource
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
            'title' => $this->title,
            'end_time' => $this->end_time,
            'stream_url' => $this->stream_url,
            'start_time' => $this->start_time,
            'description' => $this->description,
            'user' => new UserResource($this->user),
            'max_participants' => $this->max_participants,
            'instructor' => new UserResource($this->instructor),
            'resources' => LiveResourceResource::collection($this->resources),
            'participants' => ParticipantResource::collection($this->participants),
        ];
    }
}

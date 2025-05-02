<?php

namespace App\Http\Resources\Live;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class LiveResourceResource extends JsonResource
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
            'slug' => $this->slug,
            'resurce_url' => $this->resurce_url,
            'resource_name' => $this->resource_name,
            'user' => new UserResource($this->user),
            'live_session' => new LiveSessionResource($this->liveSession),
        ];
    }
}

<?php

namespace App\Http\Resources\System;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class HeroResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description, 
            'app_name' => $this->app_name, 
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at, 

            'logo' => $this->logo
                ? Storage::disk('public')->url($this->logo)
                : 'images/logo.png',

            'banner' => $this->banner
                ? Storage::disk('public')->url($this->banner)
                : 'images/Learning.jpg',

            'background_image' => $this->background_image
                ? Storage::disk('public')->url($this->background_image)
                : 'images/here-back.jpg',
        ];
    }
}

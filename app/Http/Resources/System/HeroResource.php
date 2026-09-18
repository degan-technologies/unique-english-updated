<?php

namespace App\Http\Resources\System;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Services\CloudFrontService;

class HeroResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        return [
            'id'               => $this->id,
            'title'            => $this->title,
            'description'      => $this->description, 
            'app_name'         => $this->app_name, 
            'created_at'       => $this->created_at,
            'updated_at'       => $this->updated_at,
            'deleted_at'       => $this->deleted_at, 

            // Hero images are public — plain CloudFront URL for maximum CDN cache efficiency
            'logo' => $this->logo
                ? CloudFrontService::publicUrl($this->logo)
                : 'images/logo.png',

            'banner' => $this->banner
                ? CloudFrontService::publicUrl($this->banner)
                : 'images/Learning.jpg',

            'background_image' => $this->background_image
                ? CloudFrontService::publicUrl($this->background_image)
                : 'images/here-back.jpg',
        ];
    }
}

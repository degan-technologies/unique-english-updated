<?php

namespace App\Http\Resources\Logo;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Services\CloudFrontService;
use App\Models\Logo\Logo;

class LogoResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'file_url' => $this->file_path
                ? CloudFrontService::publicUrl($this->file_path)
                : 'no-thumbnail_url.png',
            'created_at' => $this->created_at,
        ];
    }
}

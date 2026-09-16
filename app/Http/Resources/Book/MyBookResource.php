<?php

namespace App\Http\Resources\Book;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class MyBookResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tag' => $this->tag,
            'slug' => $this->slug, 
            'title' => $this->title,
            'auther' => $this->auther,
             
            'description' => $this->description,

            'intro_video_url' => $this->intro_vedio
                ? Storage::disk('s3')->temporaryUrl($this->intro_vedio,  now()->addMinutes(30))
                : 'no-intro_video.png',

            'intro_video_hls_url' => $this->hls_path
                ? Storage::disk('s3')->temporaryUrl($this->hls_path,  now()->addMinutes(30))
                : 'no-intro_video.png',

            'cover_page_url' => $this->cover_page_url
                ? Storage::disk('s3')->temporaryUrl($this->cover_page_url,  now()->addMinutes(30))
                : 'no-cover_page_url.png',

            'isDownloadable' => $this->isDownloadable,
            'download_status' => $this->download_status, 
        ];
    }
}

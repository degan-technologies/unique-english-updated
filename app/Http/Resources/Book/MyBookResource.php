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
                ? url('/api/books/stream/video/' . basename($this->intro_vedio))
                : 'no-intro_video.png',

            'cover_page_url' => $this->cover_page_url
                ? Storage::disk('public')->url($this->cover_page_url)
                : 'no-cover_page_url.png',

            'isDownloadable' => $this->isDownloadable,
            'download_status' => $this->download_status, 
        ];
    }
}

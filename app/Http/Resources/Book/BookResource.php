<?php

namespace App\Http\Resources\Book;

use App\Http\Resources\userResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookResource extends JsonResource {
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
            'price' => $this->price,
            'title' => $this->title,
            'auther' => $this->auther,
            'language' => $this->language,
            'file_url' => $this->file_url,
            'eddition' => $this->eddition,
            'discount' => $this->discount,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'not_deleted' => $this->not_deleted,
            'description' => $this->description,
            'page_number' => $this->page_number,
            'file_format' => $this->file_format,
            'publish_date' => $this->publish_date,
            'cover_page_url' => $this->cover_page_url,
            'isDownloadable' => $this->isDownloadable,
            'download_status' => $this->download_status,
            'user' => new userResource($this->whenLoaded('user')),
        ];
    }
}

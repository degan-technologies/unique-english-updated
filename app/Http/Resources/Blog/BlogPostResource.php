<?php

namespace App\Http\Resources\Blog;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class BlogPostResource extends JsonResource
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
            'slug' => $this->slug,
            'title' => $this->title,
            'excerpt' => $this->excerpt,
            'content' => $this->content, 
            'cover_image' => $this->cover_image
                ? Storage::disk('public')->url($this->cover_image)
                : 'images/no-profile.png',
            'tags' => $this->tags ?? [],
            'status' => $this->status,
            'published_at' => $this->published_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'author' => [
                'id' => $this->user?->id,
                'first_name' => $this->user?->first_name,
                'middle_name' => $this->user?->middle_name,
                'last_name' => $this->user?->last_name,
                'full_name' => trim(($this->user?->first_name ?? '') . ' ' . ($this->user?->middle_name ?? '') . ' ' . ($this->user?->last_name ?? '')),
            ],
        ];
    }
}

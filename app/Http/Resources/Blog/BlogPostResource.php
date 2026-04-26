<?php

namespace App\Http\Resources\Blog;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlogPostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $image = $this->cover_image;

        if ($image && !str_starts_with($image, 'http://') && !str_starts_with($image, 'https://')) {
            $image = asset('storage/' . ltrim($image, '/'));
        }

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'excerpt' => $this->excerpt,
            'content' => $this->content,
            'cover_image' => $image,
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

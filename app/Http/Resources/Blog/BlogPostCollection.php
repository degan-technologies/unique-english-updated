<?php

namespace App\Http\Resources\Blog;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class BlogPostCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection->map(function ($post) {
                return [
                    'id' => $post->id,
                    'title' => $post->title,
                    'slug' => $post->slug,
                    'excerpt' => $post->excerpt,
                    'content' =>$post->content,
                    'featured_image' => $post->featured_image ? asset('storage/' . $post->featured_image) : null,
                    'status' => $post->status,
                    'views_count' => $post->views_count,
                    'reading_time' => $post->reading_time,
                    'author' => [
                        'id' => $post->author->id,
                        'name' => $post->author->name,
                    ],
                    'category' => [
                        'id' => $post->category->id,
                        'name' => $post->category->name,
                        'slug' => $post->category->slug,
                    ],
                    'tags' => $post->tags->map(fn($tag) => [
                        'id' => $tag->id,
                        'name' => $tag->name,
                        'slug' => $tag->slug,
                    ]),
                    'created_at' => $post->created_at->format('Y-m-d H:i:s'),
                ];
            }),
        ];
    }
}

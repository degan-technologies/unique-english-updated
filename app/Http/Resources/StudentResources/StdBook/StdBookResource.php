<?php

namespace App\Http\Resources\StudentResources\StdBook;

use App\Http\Resources\Comment\FeedBackResource;
use App\Http\Resources\userResource;
use App\Models\Book\Book;
use App\Models\Comment\FeedBack;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
class StdBookResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        $review = FeedBack::reviewRate($this->feedbacks);

        return [
            'id' => $this->id,
            'tag' => $this->tag,
            'slug' => $this->slug,
            'price' => $this->price,
            'title' => $this->title,
            'auther' => $this->auther,
            'language' => $this->language,
            'eddition' => $this->eddition,
            'discount' => $this->discount, 
            'description' => $this->description,
            'page_number' => $this->page_number,
            'file_format' => $this->file_format,
            'publish_date' => $this->publish_date,
            'intro_vedio' => $this->intro_vedio 
            ? Storage::disk('public')->url($this->intro_vedio) 
            : 'no-video.mp4',

            'file_url' => $this->file_url
                ? Storage::disk('public')->url($this->file_url)
                : 'no-file_url.png',

            'intro_video_url' => $this->intro_vedio
                ? url('/api/books/stream/video/' . basename($this->intro_vedio))
                : 'no-intro_video.png',

            'cover_page_url' => $this->cover_page_url
            ? Storage::disk('public')->url($this->cover_page_url)
            : 'no-cover_page_url.png',

            'isMyBook' => Book::checkEligibility($this->id),
            'user' => new userResource($this->user),

            'feedBacks' => FeedBackResource::collection($this->feedbacks->sortByDesc('created_at')),
            'averageRating' => $review['averageRating'],
            'starDistribution' => $review['starDistribution'],
        ];
    }
}

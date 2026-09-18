<?php

namespace App\Http\Resources\Transaction;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Services\CloudFrontService;

class BookTransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'price' => $this->price,
            'title' => $this->title,
            'discount' => $this->discount,

            'cover_page_url' => $this->cover_page_url
            ? CloudFrontService::signedUrl($this->cover_page_url, now()->addMinutes(30))
            : 'no-cover_page_url.png',
        ];
    }
}

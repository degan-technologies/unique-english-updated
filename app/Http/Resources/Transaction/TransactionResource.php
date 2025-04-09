<?php

namespace App\Http\Resources\Transaction;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Comment\FeedBack;


class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $courseReview = $this->course && $this->course->feedbacks
        ? FeedBack::reviewRate($this->course->feedbacks)
        : ['averageRating' => 0, 'starDistribution' => []];

    // Ratings for book
    $bookReview = $this->book && $this->book->feedbacks
        ? FeedBack::reviewRate($this->book->feedbacks)
        : ['averageRating' => 0, 'starDistribution' => []];
        return [
            'id' => $this->id,
            'status' => $this->status,
            'date' => $this->created_at->format('Y-m-d'),
            'amount' => $this->amount,
            'payment_method' => $this->payment_method,
            'type' => $this->product_type,
            'ref_key' => $this->tx_ref,
            'course_id' => $this->course_id,
            'book_id' => $this->book_id,
            'user_name' => $this->user->user_name,
            'full_name' => $this->user->full_name,
            'user_id' => $this->user->id,
            'course_name' => $this->course?->course_name,
            'book_name' => $this->book?->title,
            'course_owner' => $this->course?->user?->full_name,
            'book_owner' => $this->book?->user?->full_name,
            'course' => $this->course ? [
                'averageRating' => $courseReview['averageRating'],
                'starDistribution' => $courseReview['starDistribution'],
            ] : null,

            'book' => $this->book ? [
                'averageRating' => $bookReview['averageRating'],
                'starDistribution' => $bookReview['starDistribution'],
            ] : null,

            'customer' => new CustomerInfoResource($this->customer),
        ];
    }
}
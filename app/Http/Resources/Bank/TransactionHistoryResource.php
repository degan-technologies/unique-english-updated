<?php

namespace App\Http\Resources\Bank;

use App\Http\Resources\Transaction\CustomerInfoResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Comment\FeedBack;


class TransactionHistoryResource extends JsonResource
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

            'customer' => new CustomerInfoResource($this->customer),
            'color' => $this->getColor(),
        ];
    }

    public function getColor() {
        if($this->status === TRANSACTION_SUCCESS ) {
            return 'text-green-500';
        } elseif($this->status === TRANSACTION_PENDING ) {
            return 'text-yellow-500';
        } elseif($this->status === TRANSACTION_FAILED ) {
            return 'text-red-500';
        } elseif($this->status === TRANSACTION_REFUNDED ) {
            return 'text-blue-500';
        } else {
            return 'text-gray-500';
        }
    }
}

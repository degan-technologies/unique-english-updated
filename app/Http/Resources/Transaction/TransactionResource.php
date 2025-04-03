<?php

namespace App\Http\Resources\Transaction;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
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

            'customer' => new CustomerInfoResource($this->customer),
        ];
    }
}
<?php

namespace App\Http\Resources\Bank;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransferResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'amount' => $this->amount,
            'currency' => $this->currency,
            'reference' => $this->reference,
            'narration' => $this->narration,
            'status' => $this->status,
            'date' => $this->created_at->format('M d, Y', 'H:i:s'),
        ];
    }
}

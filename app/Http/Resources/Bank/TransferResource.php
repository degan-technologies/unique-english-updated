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
            'currency' => $this->currency,
            'reference' => $this->transaction?->tx_ref ?? $this->reference,
            'deposits' => $this->deposits,
            'withdrawals' => $this->withdrawals,
            'status' => $this->status,
            'transaction_id' => $this->transaction_id,
            'date' => $this->created_at->format('M d, Y', 'H:i:s'),

            'color' => $this->getColor(),
        ];
    }

    public function getColor() {
        if ($this->status === TRANSACTION_SUCCESS) {
            return 'text-green-500';
        } elseif ($this->status === TRANSACTION_PENDING) {
            return 'text-yellow-500';
        } elseif ($this->status === TRANSACTION_FAILED) {
            return 'text-red-500';
        } elseif ($this->status === TRANSACTION_REFUNDED) {
            return 'text-blue-500';
        } else {
            return 'text-gray-500';
        }
    }
}

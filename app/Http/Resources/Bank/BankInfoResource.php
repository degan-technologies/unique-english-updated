<?php

namespace App\Http\Resources\Bank;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BankInfoResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'bank_name' => $this->bank_name,
            'bank_code' => $this->bank_code,
            'account_number' => $this->account_number, 
        ];
    }
}

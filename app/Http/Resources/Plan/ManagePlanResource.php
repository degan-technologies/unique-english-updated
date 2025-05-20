<?php
namespace App\Http\Resources\Plan;

use App\Models\Plan\Plan;
use App\Models\Transaction\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class ManagePlanResource extends JsonResource {
    public function toArray(Request $request): array {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'price' => $this->price,
            'one_to_one_price' => $this->one_to_one_price,
            'group_price' => $this->group_price,
            'duration' => $this->duration . ' months', 
            'created_at' => $this->created_at->format('l, M-d-Y'), 
        ];
    } 
}

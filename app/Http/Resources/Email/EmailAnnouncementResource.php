<?php

namespace App\Http\Resources\Email;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class EmailAnnouncementResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    { 
        return [
            "id"=> $this->id,
            'message' => $this->message,
            'subject' => $this->subject,
            'status' => $this->status,
            'created_at' => $this->formatDate($this->created_at),
            'updated_at' => $this->formatDate($this->updated_at)
        ];
    }

     protected function formatDate($date): ?string {
        if (is_string($date)) {
            $date = Carbon::parse($date);
        }
        
        return $date?->format('l, F j, Y');
    }
} 
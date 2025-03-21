<?php

namespace App\Http\Resources\Course;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class CourseContentResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {

        $data =  [
            'id' => $this->id,
            'slug' => $this->slug,
            'module_id' => $this->course_module_id,
            'course_id' => $this->course_id,          
            'title' => $this->title,
            'sequence' => $this->sequence,
            'note' => $this->note,
            'course_content_url' => $this->content_url 
            ? url('/api/coursecontent/stream/video/' . basename($this->content_url))
            : 'no-intro_video.png',

            'thumbnail_url' => $this->thumbnail_url
                ? Storage::disk('public')->url($this->thumbnail_url)
                : 'no-thumbnail_url.png',
        ];

        // if($this->checkEligibility()) {
        //         $data['hour'] = $this->hour;
        //         $data['description'] = $this->description;
        //         $data['content_type'] = $this->content_type;
        //         $data['status'] = $this->status;
        //         $data['note'] = $this->note;
        //         $data['content_url'] = $this->content_url
        //             ? Storage::disk('public')->url($this->content_url)
        //             : 'no-content_url.png';
        //     } 

        return $data;
    }

    
}

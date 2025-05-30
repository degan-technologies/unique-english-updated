<?php

namespace App\Http\Resources\StudentResources\StdBook;

use App\Http\Resources\Comment\FeedBackResource;
use App\Http\Resources\userResource;
use App\Models\Book\Book;
use App\Models\Comment\FeedBack;
use App\Models\Transaction\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
class StdBookResource extends JsonResource {
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        $review = FeedBack::reviewRate($this->feedbacks);

        return [
            'id' => $this->id,
            'tag' => $this->tag,
            'slug' => $this->slug,
            'price' => $this->price,
            'title' => $this->title,
            'auther' => $this->auther,
            'language' => $this->language,
            'eddition' => $this->eddition,
            'discount' => $this->discount, 
            'description' => $this->description,
            'page_number' => $this->page_number,
            'file_format' => $this->file_format,
            'publish_date' => $this->publish_date,
            'total_enroll' => $this->totalEnroll($this->id),
            'revenue' => $this->totalRevenue($this->id),

            'file_url' => $this->getFileUrl(),

            'intro_video_url' => $this->intro_vedio
                ? url('/api/books/stream/video/' . basename($this->intro_vedio))
                : 'no-intro_video.png',

            'cover_page_url' => $this->cover_page_url
            ? Storage::disk('public')->url($this->cover_page_url)
            : 'no-cover_page_url.png',

            'isMyBook' => Book::checkEligibility($this->id),
            'user' => new userResource($this->user),

            'feedBacks' => FeedBackResource::collection($this->feedbacks->sortByDesc('created_at')),
            'averageRating' => $review['averageRating'],
            'starDistribution' => $review['starDistribution'],
        ];
    }

    public function totalEnroll($id) {

        $countTotalEnroll = Transaction::query()
            ->where('book_id', $id)
            ->where('status', 'success')
            ->count();

        return $countTotalEnroll === 0 ?  'not selled' : $countTotalEnroll;
    }

    public function totalRevenue($id) {
        $countRevenue = Transaction::query()
            ->where('book_id', $id)
            ->where('status','success')
            ->sum('amount');

        return $countRevenue === 0 ?  'not selled' : $countRevenue;
    }

    public function getFileUrl() {
        $user = User::query()
            ->where('id', Auth::id())
            ->has('systemAdmin')
            ->first(); 

        if(Book::checkEligibility($this->id) || $user !== null ) {
            return $this->file_url
                ? Storage::disk('public')->url($this->file_url)
                : 'no-file_url.png';
        } 

        return 'not-allowed.png';
    }
}

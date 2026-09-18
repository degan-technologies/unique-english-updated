<?php

namespace App\Http\Resources\StudentResources\StdBook;

use App\Helper\TokenGenerator;
use App\Http\Resources\Comment\FeedBackResource;
use App\Http\Resources\userResource;
use App\Models\Book\Book;
use App\Models\Comment\FeedBack;
use App\Models\Transaction\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use App\Services\CloudFrontService;
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
                ? CloudFrontService::signedUrl($this->intro_vedio, now()->addMinutes(60))
                : 'no-intro_video.png',

            // HLS master playlist — plain CloudFront URL; cookies sent separately
            'intro_video_hls_url' => $this->hls_path
                ? CloudFrontService::hlsUrl($this->hls_path)
                : 'no-intro_video.png',

            'cover_page_url' => $this->cover_page_url
            ? CloudFrontService::signedUrl($this->cover_page_url, now()->addMinutes(120))
            : 'no-cover_page_url.png',

            'isMyBook' => Book::checkEligibility($this->id),
            'user' => new userResource($this->user),

            'averageRating' => $review['averageRating'],
            'starDistribution' => $review['starDistribution'],
        ];
    }

    public function totalEnroll($id) {

        if (isset($this->total_enroll)) {
            return $this->total_enroll === 0 ? 'not selled' : $this->total_enroll;
        }

        $countTotalEnroll = Transaction::query()
            ->where('book_id', $id)
            ->where('status', 'success')
            ->count();

        return $countTotalEnroll === 0 ?  'not selled' : $countTotalEnroll;
    }

    public function totalRevenue($id) {
        if (isset($this->total_revenue)) {
            return $this->total_revenue === 0 ? 'not selled' : $this->total_revenue;
        }

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

        if (!$user) {
             return 'not-allowed.png';
        }

        $checklegibility = Book::checkEligibility($this->id);

        if(!$checklegibility) { 
            return 'not-allowed.png';
        }
 
        return  TokenGenerator::generateSecurePdfUrl('book.pdf', basename($this->file_url), Auth::id());
    }
}

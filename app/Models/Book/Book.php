<?php

namespace App\Models\Book;

use App\Models\Comment\FeedBack;
use App\Models\Transaction\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Book extends Model {
   
    use HasFactory, SoftDeletes;
    protected $table = 'books'; 
     protected $fillable = [
        'slug', 'title', 'auther', 'page_number', 'publish_date',
        'eddition', 'price', 'discount', 'description', 'language',
        'file_format', 'cover_page_url', 'file_url', 'tag',
        'isDownloadable', 'download_status', 'user_id','intro_vedio',
        'video_optimized', 
    ];

    protected $casts = [
        'tag' => 'array',
        'price' => 'integer',
        'discount' => 'integer',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function transactions() { return $this->hasMany(Transaction::class); }
    public function getNotDeletedAttribute() { return !$this->deleted_at ? 1 : 0; }
    public function getDownloadStatusAttribute() { return $this->isDownloadable ? 1 : 0; }
    public function feedBacks() { return $this->hasMany(FeedBack::class); }

    public static function checkEligibility($bookId) {
        $user = Auth::guard('api')->user();
        
        if (!$user) {
            return false;
        }

        $myTransactions = Transaction::query()
            ->where('customer_id', $user->id)
            ->where('book_id', $bookId)
            ->where('product_type', BOOK)
            ->where('status', TRANSACTION_SUCCESS)
            ->first(); 

        if ($myTransactions) {
            return true;
        }

        return false;
    }
}

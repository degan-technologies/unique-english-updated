<?php

namespace App\Models\Transaction;

use App\Models\Course\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use App\Models\Book\Book;
use App\Models\Live\Live;


class Transaction extends Model 
{
    protected $fillable = [
        'slug', 'amount', 'transaction_type', 'status', 'payment_method', 'product_type',
        'tx_ref', 'payment_url', 'enrolled_at', 'user_id',
        'customer_id', 'course_id', 'book_id', 'live_id'
    ];

    public function enrollments() { return $this->morphTo(); }

    public function customer() { return $this->belongsTo(User::class, 'customer_id'); }
    public function course() { return $this->belongsTo(Course::class); }
    public function transfer() { return $this->hasOne(Transfer::class); }
    public function user() { return $this->belongsTo(User::class, 'user_id');}
    public function book() { return $this->belongsTo(Book::class, 'book_id');}
    public function live() { return $this->belongsTo(Live::class, 'live_id');}
}

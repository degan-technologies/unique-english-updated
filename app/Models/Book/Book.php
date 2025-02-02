<?php

namespace App\Models\Book;

use App\Models\Transaction\Transaction;
use Illuminate\Database\Eloquent\Model;

class Book extends Model {
    protected $fillable = [
        'slug', 'enrolled_at', 'not_deleted',
        'user_id', 'book_id',
    ];

    public function transaction() { return $this->hasOne(Transaction::class); }
}

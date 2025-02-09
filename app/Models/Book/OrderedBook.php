<?php

namespace App\Models\Book;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class OrderedBook extends Model {

    protected $fillable = [
        'slug', 'enrolled_at', 'user_id', 'book_id',
    ];
    
    protected $casts = [
        'enrolled_at' => 'datetime',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function books() { return $this->belongsTo(Book::class); }
    public function getNotDeletedAttribute() { return !$this->deleted_at ? 1 : 0; }

}

<?php

namespace App\Models\Book;

use App\Models\Transaction\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Book extends Model {
   
    use HasFactory, SoftDeletes;
    protected $table = 'books'; // If your table name is 'books'


    protected $fillable = [
        'slug', 'title', 'auther', 'page_number', 'publish_date',
        'eddition', 'price', 'discount', 'description', 'language',
        'file_format', 'cover_page_url', 'file_url', 'tag',
        'isDownloadable', 'download_status', 'user_id',
    ];

    protected $casts = [
        'tag' => 'array',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function transaction() { return $this->hasOne(Transaction::class); }
    public function getNotDeletedAttribute() { return !$this->deleted_at ? 1 : 0; }
    public function getDownloadStatusAttribute() { return $this->isDownloadable ? 1 : 0; }

}

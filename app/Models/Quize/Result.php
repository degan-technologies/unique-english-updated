<?php

namespace App\Models\Quize;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Result extends Model {
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'slug',
        'result',
        'user_id',
        'quize_id',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function quize() { return $this->belongsTo(Quize::class,); }
}

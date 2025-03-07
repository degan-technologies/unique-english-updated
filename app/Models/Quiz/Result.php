<?php

namespace App\Models\Quiz;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Result extends Model {
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'slug',
        'result',
        'user_id',
        'q_meta_data_id',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function qMetaData() { return $this->belongsTo(QMetaData::class,); }
}

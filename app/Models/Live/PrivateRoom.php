<?php

namespace App\Models\Live;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class PrivateRoom extends Model
{
    protected $fillable = [
        'class_name',
        'instructor_id',
        'user_id'
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function instructor() { return $this->belongsTo(User::class,'instructor_id'); }
}

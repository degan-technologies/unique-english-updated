<?php

namespace App\Models\System;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class PlatformComission extends Model {
    protected $fillable = [
        'fees', 'user_id'
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function platformComissionHistories() { return $this->hasMany(PlatformComissionHistory::class); }
}

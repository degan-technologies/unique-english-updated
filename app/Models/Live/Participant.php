<?php

namespace App\Models\Live;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Participant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'joined_at','status','user_id','live_session_id',
    ];
    public function user(){ return $this->belongsTo(User::class);}
    public function liveSession(){ return $this->belongsTo(LiveSession::class);}
}

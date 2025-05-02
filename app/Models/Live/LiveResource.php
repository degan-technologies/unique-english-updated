<?php

namespace App\Models\Live;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LiveResource extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'slug','resource_name','resurce_url','user_id','live_session_id',
    ];

    public function user(){ return $this->belongsTo(User::class);}
    public function liveSession(){ return $this->belongsTo(LiveSession::class);}
}

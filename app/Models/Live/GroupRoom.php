<?php

namespace App\Models\Live;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class GroupRoom extends Model
{
    protected $fillable = [
        'user_id', 'live_room_id'
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function liveRoom() { return $this->belongsTo(LiveRooms::class); }
}

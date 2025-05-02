<?php

namespace App\Models\Live;

use App\Models\Schedule\Schedule;
use Illuminate\Database\Eloquent\Model;

class PeredicTable extends Model
{
    protected $fillable = [
        'schedule_id',
        'live_room_id', 
    ];

    public function schedule() { return $this->belongsTo(Schedule::class); }
    public function liveRoom() { return $this->belongsTo(LiveRooms::class); }   
}

<?php

namespace App\Models\Live;

use Illuminate\Database\Eloquent\Model;

class PrivateRoom extends Model
{
    protected $fillable = [
        'schedule_id',
        'live_room_id'
    ];
}

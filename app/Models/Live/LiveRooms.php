<?php

namespace App\Models\Live;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class LiveRooms extends Model {
    
    protected $fillable = ['class_name', 'instructor_id', 'user_id'];

    public function user() { return $this->belongsTo(User::class,'instructor_id'); }
    public function instructor() { return $this->belongsTo(User::class,'instructor_id'); }
    public function groupRooms() { return $this->hasMany(GroupRoom::class, 'live_room_id');}
    public function peredicTables() { return $this->hasMany(PeredicTable::class, 'live_room_id');}

}

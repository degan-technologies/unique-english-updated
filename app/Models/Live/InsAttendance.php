<?php

namespace App\Models\Live;

use App\Models\Schedule\Schedule;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class InsAttendance extends Model {
    
    protected $fillable = [
        'start_time',
        'end_time',
        'schedule_id',
        'instructor_id',
        'visiter_id'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

     public function schedule() { return $this->belongsTo(Schedule::class,); }
    public function instructor() { return $this->belongsTo(User::class,'instructor_id'); }
    public function visiter() { return $this->belongsTo(User::class,'visiter_id'); }
    public function stdAttendances() { return $this->hasMany(StdAttendance::class,'ins_attendance_id'); }
}

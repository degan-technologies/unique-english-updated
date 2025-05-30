<?php

namespace App\Models\Live;

use App\Models\Schedule\Schedule;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class StdAttendance extends Model {
    
    protected $fillable = [
        'start_time',
        'end_time',
        'ins_attendance_id',
        'student_id',
        'schedule_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];


    public function schedule() { return $this->belongsTo(Schedule::class,); }
    public function student() { return $this->belongsTo(User::class,'student_id'); }
    public function attendance() { return $this->belongsTo(InsAttendance::class,'ins_attendance_id'); }
}

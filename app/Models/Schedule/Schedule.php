<?php

namespace App\Models\Schedule;

use App\Models\Live\PeredicTable;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model {
    use HasFactory;

    protected $fillable = [
        'day', 'time', 
        'user_id', 'schedule_time',
        'room_name'
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function peredicTable() { return $this->hasOne(PeredicTable::class); }
}

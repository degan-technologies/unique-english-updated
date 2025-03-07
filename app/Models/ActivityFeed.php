<?php

namespace App\Models;

use App\Models\Course\Course;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ActivityFeed extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'course_id', 'type', 'action', 'description'];
    public function user(){ return $this->belongsTo(User::class); }
    public function course(){return $this->belongsTo(Course::class); }
}

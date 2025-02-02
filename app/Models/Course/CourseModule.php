<?php

namespace App\Models\Course;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class CourseModule extends Model {
    protected $fillable = [
       'title', 'sequence', 'description',
       'user_id', 'course_id',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function course() { return $this->belongsTo(Course::class); }
    public function courseContents() { return $this->hasMany(CourseContent::class); }
}

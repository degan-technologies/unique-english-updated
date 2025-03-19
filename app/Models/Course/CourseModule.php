<?php

namespace App\Models\Course;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use App\Models\Quiz\QMetaData;


class CourseModule extends Model {
    protected $fillable = [
       'title', 'sequence', 'description',
       'user_id', 'course_id','slug'
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function course() { return $this->belongsTo(Course::class); }
    public function QMetaDatas() { return $this->hasMany(QMetaData::class); }
    public function courseContents() { return $this->hasMany(CourseContent::class); }
}

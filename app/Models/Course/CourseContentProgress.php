<?php

namespace App\Models\Course;

use Illuminate\Database\Eloquent\Model;


class CourseContentProgress extends Model
{
    protected $table = 'course_content_progress';

    protected $fillable = [
        'user_id',
        'course_content_id',
        'progress',
        'course_id',
        'course_module_id',
        'vedeo_progress'
    ];

    public function courseContent() { return $this->belongsTo(CourseContent::class); }
}

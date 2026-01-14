<?php

namespace App\Models\Course;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class CourseContent extends Model
{

    protected $fillable = [
        'slug', 'course_id', 'sequence', 'title', 'description',
        'content_type', 'content_url', 'thumbnail_url', 'hour', 'status',
        'isDownloadable', 'user_id', 'course_module_id', 'video_optimized'
    ];

    protected $casts = [
        'content_type' => 'integer',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function course() { return $this->belongsTo(Course::class); }
    public function courseContentProgress() { return $this->hasOne(CourseContentProgress::class); }
    public static function timeToSeconds($time) {
        if (!$time) {
            return 0;
        }

        $timeParts = explode(':', $time);
        return $timeParts[0] * 3600 + $timeParts[1] * 60 + $timeParts[2];
    }
}

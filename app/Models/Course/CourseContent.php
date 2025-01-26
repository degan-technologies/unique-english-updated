<?php

namespace App\Models\Course;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class CourseContent extends Model {
    
    protected $fillable = [
        'slug', 'course_id','sequence', 'title', 'description',
        'content_type', 'content_url', 'thumbnail_url',
        'hour', 'status', 'note', 'isDownloadable', 'user_id'
    ];

    public function user() { return $this->belongsTo(User::class); }
}
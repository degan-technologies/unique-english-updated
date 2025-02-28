<?php

namespace App\Models\Quiz;

use App\Models\Course\Course;
use App\Models\Course\CourseContent;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QMetaData extends Model {
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'slug', 'title', 'instraction', 'question_type',
        'user_id', 'course_content_id', 'course_id'
    ];
    public function user() { return $this->belongsTo(User::class); }
    public function quizzes(){ return $this->hasMany(Quiz::class,); }
    public function course() { return $this->belongsTo(Course::class);}
    public function courseContent() { return $this->belongsTo(CourseContent::class);}
}

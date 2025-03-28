<?php

namespace App\Models\Quiz;

use App\Models\Course\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QASection extends Model {
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'question',
        'user_id',
        'course_id',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function course() { return $this->belongsTo(Course::class); }
    public function answers() { return $this->hasMany(Answer::class, 'question_id'); }
    public function parentSection() { return $this->belongsTo(QASection::class); }
}

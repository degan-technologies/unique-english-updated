<?php

namespace App\Models\Quiz;

use App\Models\Course\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Answer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'answer',        // The answer text
        'question_id',   // Foreign key reference to the question (QASection)
        'course_id',     // Foreign key reference to the course
        'user_id',       // Foreign key reference to the user who answered
    ];

    // Relationship with the question (QASection)
    public function question()
    {
        return $this->belongsTo(QASection::class, 'question_id');
    }

    // Relationship with the user who answered
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship with the course
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    // Soft delete functionality
    public function getDeletedAtColumn()
    {
        return 'deleted_at'; // Ensuring the soft delete is handled
    }
}

<?php

namespace App\Models\Quiz;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Quiz extends Model {
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'slug',
        'question_type',
        'question',
        'choice',
        'answer',
        'user_id',
        'q_meta_data_id',
        'hint',
    ];

    protected $casts = [
        'choice' => 'array',
        'answer' => 'array',
    ];

    public function user(){ return $this->belongsTo(User::class); }

    public function results() { return $this->hasMany(Result::class); }
    public function metaData() { return $this->belongsTo(QMetaData::class); }
    public function quizAnswers() { return $this->hasMany(QuizAnswer::class); }
}
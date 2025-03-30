<?php

namespace App\Models\Quiz;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class QuizAnswer extends Model {
    
    protected $fillable = [
        'user_id',  
        'quiz_id', 
        'answer', 
        'choice',  
    ];

    protected $casts = [
        'choice' => 'array', 
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function quiz() { return $this->belongsTo(Quiz::class); }

}

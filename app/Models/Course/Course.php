<?php

namespace App\Models\Course;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Course extends Model {

    protected $fillable = [
        'slug','course_name', 'overview', 'tag',
        'skill_level', 'price', 'discount',
        'credit_hour', 'user_id',
    ];
    
    public function user() {return $this->belongsTo(User::class);}
}

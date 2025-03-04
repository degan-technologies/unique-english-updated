<?php

namespace App\Models\Course;

use App\Models\Comment\FeedBack;
use App\Models\Transaction\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Course extends Model {

    protected $fillable = [
        'slug','course_name', 'overview', 'tag',
        'skill_level', 'price', 'discount',
        'credit_hour', 'user_id','thumbnail_url','language', 'intro_video','status'
    ];
    
    public function user() {return $this->belongsTo(User::class);}
    public function courseModules() { return $this->hasMany(CourseModule::class); }
    public function courseContents() { return $this->hasMany(CourseContent::class); }
    public function transactions() { return $this->hasMany(Transaction::class); }
    public function feedBacks() { return $this->hasMany(FeedBack::class); }
}

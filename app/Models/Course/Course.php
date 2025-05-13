<?php

namespace App\Models\Course;

use App\Models\Comment\FeedBack;
use App\Models\Quiz\QASection;
use App\Models\Transaction\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

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
    public function courseContentProgress() { return $this->hasMany(CourseContentProgress::class); }
    public function qaSections() { return $this->hasMany(QASection::class); }

    public static function checkEligibility($courseId) {
        $user = Auth::guard('api')->user();

        if (!$user) {
            return false;
        }

        $myTransactions = Transaction::query()
            ->where('customer_id', $user->id)
            ->where('course_id', $courseId)
            ->where('product_type', COURSE)
            ->where('status', TRANSACTION_SUCCESS)
            ->first();

        if ($myTransactions) {
            return true;
        }

        return false;
    }

    public static function getCourseProgress($slug){
        $user = Auth::user();
        $overAllPogress = 0;

        $course = Course::query()
            ->where('slug', $slug)
            ->first();

        $eligibleCourse = Course::checkEligibility($course->id);

        if (!$eligibleCourse) {
            return response()->json(['message' => 'Course not found'], 404);
        }

        $progress = CourseContentProgress::query()
            ->where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->orderBy('id', 'desc')
            ->first();
 
        $totalSeconds = $course->courseContents()->where('content_type', VIDEO)->get()->reduce(function ($carry, $content) {
            $timeParts = explode(':', $content->hour);
            $seconds = ($timeParts[0] * 3600) + ($timeParts[1] * 60) + $timeParts[2];
            return $carry + $seconds;
        }, 0);

        $courseHours = floor($totalSeconds / 3600);
        $courseMinutes = floor(($totalSeconds % 3600) / 60);
        $courseSeconds = $totalSeconds % 60;

        $overAllCreditHour = sprintf('%02d:%02d:%02d', $courseHours, $courseMinutes, $courseSeconds);

        if ($progress) {
            $totalTimeInSeconds = $progress->where('course_id', $course->id)->get()->reduce(function ($carry, $content) {
                $timeParts = explode(':', $content->progress);
                $seconds = ($timeParts[0] * 3600) + ($timeParts[1] * 60) + $timeParts[2];
                return $carry + $seconds;
            }, 0);

            $overAllPogress = $totalTimeInSeconds / $totalSeconds * 100; 
        }

        

        if (!$overAllPogress) {
            $overAllPogress = 0;
        }
        
        return [
            'overAllPogress' => $overAllPogress,
            'overAllCreditHour' => $overAllCreditHour,
            'progress' => $progress,
            'course' => $course,
        ];
    }
}



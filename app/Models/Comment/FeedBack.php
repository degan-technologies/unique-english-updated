<?php

namespace App\Models\Comment;

use App\Models\Course\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeedBack extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'feed_backs';

    protected $fillable = [
        'rate',
        'comment',
        'user_id',
        'instractor_id',
        'course_id',
        'likes',
        'dislikes',
        'reports',
        'report_issue_type',
        'report_issue_details'
    ];

    protected $casts = [
        'rate'     => 'float',
        'likes'    => 'integer',
        'dislikes' => 'integer',
        'reports'  => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instractor_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function feedbackUserInteractions () { return $this->hasMany(FeedbackUserInteraction::class); }


    public static function reviewRate($feedBacks){
        // Compute average rating (supports half-star values)
        $averageRating = $feedBacks->avg('rate') ?? 0;

        // Compute star distribution (round ratings to nearest whole number)
        $distribution = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
        foreach ($feedBacks as $feedBack) {
            $rounded = round($feedBack->rate);
            if (isset($distribution[$rounded])) {
                $distribution[$rounded]++;
            }
        }
        $starDistribution = [
            $distribution[5],
            $distribution[4],
            $distribution[3],
            $distribution[2],
            $distribution[1],
        ];

        return  [
            'averageRating'    => round($averageRating, 1),
            'starDistribution' => $starDistribution,
        ];
    } 
}

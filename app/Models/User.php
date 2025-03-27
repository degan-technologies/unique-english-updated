<?php

namespace App\Models;

use App\Models\Bank\BankInfo;
use App\Models\Book\Book;
use App\Models\Book\OrderedBook;
use App\Models\Comment\FeedBack;
use App\Models\Comment\FeedbackUserInteraction;
use App\Models\Course\Course;
use App\Models\Course\CourseContent;
use App\Models\Course\CourseContentProgress;
use App\Models\Course\CourseModule;
use App\Models\Role\Instructor;
use App\Models\Role\Student;
use App\Models\Role\SystemAdmin;
use App\Models\Live\LiveSession;
use App\Models\Live\Participant;
use App\Models\Live\LiveResource;

use App\Models\Quiz\Quiz;
use App\Models\Quiz\Result;
use App\Models\Quiz\QASection;
use App\Models\Quiz\QMetaData;
use App\Models\System\PlatformComission;
use App\Models\System\PlatformComissionHistory;
use App\Models\Transaction\Transaction;
use App\Models\Transaction\Transfer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable {
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'slug',
        'gender',
        'email',
        'password',
        'temp_password',
        'first_name',
        'middle_name',
        'last_name',
        'user_name',
        'full_name',
        'phone',
        'profile',
        'bg_image',
        'role',
        'user_banned_at',
        'progress',
        'provider',
        'provider_id',
        'email_verified_at',
        'otp',
        'otp_expires_at',
        'otp_attempts',

    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        // We are not adding temp_password here because you want it visible
        // until the instructor updates their password.
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'progress' => 'integer',
        ];
    }

    public function student() { return $this->hasOne(Student::class); }
    public function instructor() { return $this->hasOne(Instructor::class); }
    public function systemAdmin() { return $this->hasOne(SystemAdmin::class); }

    public function courses() { return $this->hasMany(Course::class); }
    public function liveSessions() { return $this->hasMany(LiveSession::class);}
    public function participants() { return $this->hasMany(Participant::class);}
    public function liveResources() { return $this->hasMany(LiveResource::class);}
    public function courseContents() { return $this->hasMany(CourseContent::class); }
    public function courseModules() { return $this->hasMany(CourseModule::class); }
    public function transaction() { return $this->hasMany(Transaction::class); }

    public function quizzes() { return $this->hasMany(Quiz::class);}
    public function results() { return $this->hasMany(Result::class);}
    public function qaSections() { return $this->hasMany(QASection::class); }

    public function books() { return $this->hasMany(Book::class); }
    public function qMetaDatas() { return $this->hasMany(QMetaData::class); }

    // public function books() { return $this->hasMany(Book::class); }
    public function orderedBooks() { return $this->hasMany(OrderedBook::class); }
    public function transactions() { return $this->hasMany(Transaction::class); }
    public function bankInfos() { return $this->hasMany(BankInfo::class); }
    public function transfers() { return $this->hasMany(Transfer::class); }

    public function platformComissions() { return $this->hasOne(PlatformComission::class); }
    public function platformComissionHistories() { return $this->hasOne(PlatformComissionHistory::class); }
    public function feedBacks() { return $this->hasMany(FeedBack::class); }
    public function feedbackUserInteractions () { return $this->hasMany(FeedbackUserInteraction::class); }

    public function courseContentProgress() { return $this->hasMany(CourseContentProgress::class); }

    public function scopeWhereSystemAdminOrInstructor(Builder $query, $userId = null) {
        if($userId == null){
            $userId = Auth::id();
        }

        return $query
            ->where('id', $userId)
            ->where(function ($query){
                $query
                    ->orWhere(fn($subQuery) => $subQuery->has('systemAdmin'))
                    ->orWhere(fn($subQuery) => $subQuery->has('instructor'));
            });
    }
}

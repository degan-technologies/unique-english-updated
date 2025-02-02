<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Models\Course\Course;
use App\Models\Course\CourseContent;
use App\Models\Course\CourseModule;
use App\Models\Course\Enrollment;
use App\Models\Role\Instructor;
use App\Models\Role\Student;
use App\Models\Role\SystemAdmin;
// use Illuminate\Contracts\Auth\MustVerifyEmail
use App\Models\Live\LiveSession;
use App\Models\Live\Participant;
use App\Models\Live\LiveResource;
use App\Models\Transaction\Transaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
class User extends Authenticatable {
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'slug', 'gender', 'email', 'password',
        'first_name', 'middle_name', 'last_name',
        'user_name', 'full_name', 'phone',
        'profile', 'bg_image', 'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
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
    public function enrollments() { return $this->hasMany(Enrollment::class); }



    public function scopeWhereSystemAdminOrInstructor(Builder $query, $userId = null) {
        if($userId == null){
            $userId = Auth::id();
        }

        return $query
            ->where('user_id', $userId)
            ->where(function ($query){
                $query
                    ->orWhere(fn($subQuery) => $subQuery->has('systemAdmin'))
                    ->orWhere(fn($subQuery) => $subQuery->has('instructor'));
            });
    }


}


<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Live\ParticipantController;
use App\Http\Controllers\Live\LiveSessionController;
use App\Http\Controllers\Live\LiveResourceController;
use App\Http\Controllers\Live\VertualClassEnrollmentController;

Route::post('/login', [AuthController::class, 'login']);

Route::post('/registration', [UserController::class, 'store']);

Route::middleware('auth:api')
    ->group(function () {
        Route::post('/add-instructor', [UserController::class, 'addInstructor']);
        Route::delete('/delete-instructor/{id}', [UserController::class, 'destroy']);
        Route::post('/update-profile', [UserController::class, 'profileUpdate']);
        Route::post('/password-reset', [UserController::class, 'passwordReset']);
    });

Route::middleware('auth:api')
    ->group(function () {
        Route::get('/current', [AuthController::class, 'currentUser']);
        Route::resource('live-sessions', LiveSessionController::class);
        Route::resource('live-resources', LiveResourceController::class);
        Route::resource('participants', ParticipantController::class);
        Route::resource('virtual-class-enrollments', VertualClassEnrollmentController::class);
    });

Route::middleware('auth:api')
    ->prefix('courses')
    ->group(function () {
        Route::resource('/course', CourseController::class);
        Route::resource('/content', CourseContentController::class);
    });
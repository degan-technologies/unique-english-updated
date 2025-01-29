<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Course\CourseContentController;
use App\Http\Controllers\Course\CourseController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

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
    });

Route::middleware('auth:api')
    ->prefix('courses')
    ->group(function () {
        Route::resource('/course', CourseController::class);
        Route::resource('/content', CourseContentController::class);
    });
<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Course\CourseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:api')
    ->group(function () {
        Route::get('/current', [AuthController::class, 'currentUser']);
    });

Route::middleware('auth:api')
    ->prefix('courses')
    ->group(function () {
        Route::resource('/course', CourseController::class);
    });
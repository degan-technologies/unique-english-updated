<?php

use App\Helper\Lang\Back\Amharic;
use App\Helper\Lang\Back\English;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\SocialController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Home and Login views
Route::get('/', fn() => view('welcome'))->name('home');
Route::get('/login', fn() => view('welcome'))->name('login');

// Auth routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [UserController::class, 'studentRegistration']);

// Language translations
Route::get('/language/{lang}', function ($lang) {
    return $lang == 'am' ? Amharic::translations() : English::translations();
});

// Social login
Route::get('/auth/{provider}/redirect', [SocialController::class, 'redirectToProvider'])->name('social.redirect');
Route::get('/auth/{provider}/callback', [SocialController::class, 'handleProviderCallback'])->name('social.callback');

// Email preview route (for testing)
Route::get('/email', function () {
    return view('emails.InstructorNotificationEmail');
});

// Password reset
Route::post('/forgot-password', [AuthController::class, 'sendResetOtp']);
Route::post('/reset-password', [AuthController::class, 'resetPasswordViaOtp']);

// Not Found page
Route::get('/not-found', function () {
    return view('notFound');
});

// Fallback route for unknown paths
Route::fallback(function () {
    return redirect()->route('home');
});

<?php

use App\Helper\Lang\Back\Amharic;
use App\Helper\Lang\Back\English;
use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => view('welcome'));
Route::get('/login', fn() => view('welcome'))->name('login');

Route::post('/login', [AuthController::class, 'login']);
Route::get('/language/{lang}', function ($lang) {
    return $lang == 'am' ? Amharic::translations() : English::translations();
});

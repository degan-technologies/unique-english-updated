<?php

use App\Helper\Lang\Back\Amharic;
use App\Helper\Lang\Back\English;
use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', [AuthController::class, 'login'])->name('login');

Route::get('/language/{lang}', function ($lang) {
    return $lang == 'am' ? Amharic::translations() : English::translations();
});

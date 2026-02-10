<?php

use App\Http\Controllers\Auth\InstagramAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/auth/instagram', [InstagramAuthController::class, 'redirect'])->name('auth.instagram');
Route::get('/auth/instagram/redirect', [InstagramAuthController::class, 'redirect'])->name('auth.instagram.redirect');
Route::get('/auth/instagram/callback', [InstagramAuthController::class, 'callback'])->name('auth.instagram.callback');

require __DIR__.'/settings.php';
